<?php

use App\Enums\TicketPriority;
use App\Exceptions\UrgencyProviderException;
use App\Services\Urgency\OpenAiUrgencyAnalyzer;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['urgency.api_key' => 'test-key', 'urgency.model' => 'gpt-4o-mini', 'urgency.connect_timeout' => 2, 'urgency.timeout' => 5]);
});

test('sends only the description with a strict schema and bounded request options', function () {
    Http::preventStrayRequests();
    $fixture = json_decode(file_get_contents(__DIR__.'/../Fixtures/openai-completed.json'), true);
    Http::fake(['https://api.openai.com/v1/responses' => function (Request $request, array $options) use ($fixture) {
        expect($options['timeout'])->toBe(5.0);
        expect($options['connect_timeout'])->toBe(2.0);
        expect($options['allow_redirects'])->toBeFalse();

        return Http::response($fixture);
    }]);

    $result = (new OpenAiUrgencyAnalyzer)->analyze('Description de test.');

    expect($result->priority)->toBe(TicketPriority::High);
    expect($result->source)->toBe('llm');
    expect($result->reason)->toBe('Service indisponible.');
    Http::assertSentCount(1);
    Http::assertSent(function (Request $request) {
        expect($request->data()['input'])->toBe([
            ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => 'Description de test.']]],
        ]);
        expect($request->data()['store'])->toBeFalse();
        expect($request->data()['max_output_tokens'])->toBe(256);
        expect($request->data()['text']['format']['strict'])->toBeTrue();
        expect($request->data()['text']['format']['schema']['additionalProperties'])->toBeFalse();
        expect($request->data()['text']['format']['schema']['required'])->toBe(['priority', 'reason']);

        return $request->method() === 'POST' && $request->hasHeader('Authorization', 'Bearer test-key');
    });
});

test('rejects non-success HTTP responses without retrying', function (int $status) {
    Http::preventStrayRequests();
    Http::fake(['https://api.openai.com/v1/responses' => Http::response(['error' => 'SENSITIVE-PROVIDER'], $status)]);

    expect(fn () => (new OpenAiUrgencyAnalyzer)->analyze('Description.'))->toThrow(UrgencyProviderException::class);

    Http::assertSentCount(1);
})->with([301, 401, 403, 429, 500, 503]);

test('rejects incomplete malformed refused or oversized provider data', function (string $variant) {
    Http::preventStrayRequests();
    $data = json_decode(file_get_contents(__DIR__.'/../Fixtures/openai-completed.json'), true);
    $body = match ($variant) {
        'incomplete' => array_replace($data, ['status' => 'incomplete']),
        'refusal' => ['status' => 'completed', 'output' => [['type' => 'message', 'role' => 'assistant', 'status' => 'completed', 'content' => [['type' => 'refusal', 'refusal' => 'No']]]]],
        'empty' => [],
        'oversized' => str_repeat('x', 65537),
        'invalid body' => 'not-json',
        default => $data,
    };
    if (in_array($variant, ['unknown priority', 'empty reason', 'long reason', 'extra property', 'invalid output'], true)) {
        $result = match ($variant) {
            'unknown priority' => ['priority' => 'critical', 'reason' => 'Impact.'],
            'empty reason' => ['priority' => 'high', 'reason' => ' '],
            'long reason' => ['priority' => 'high', 'reason' => str_repeat('é', 501)],
            'extra property' => ['priority' => 'high', 'reason' => 'Impact.', 'command' => 'do-something'],
            default => null,
        };
        $body['output'][0]['content'][0]['text'] = $result === null ? 'not-json' : json_encode($result);
    }
    Http::fake(['https://api.openai.com/v1/responses' => Http::response($body)]);

    expect(fn () => (new OpenAiUrgencyAnalyzer)->analyze('Description.'))->toThrow(UrgencyProviderException::class);

    Http::assertSentCount(1);
})->with(['incomplete', 'refusal', 'empty', 'oversized', 'invalid body', 'unknown priority', 'empty reason', 'long reason', 'extra property', 'invalid output']);

test('translates connection and timeout failures into controlled provider failures', function () {
    Http::preventStrayRequests();
    Http::fake(['https://api.openai.com/v1/responses' => Http::failedConnection()]);

    expect(fn () => (new OpenAiUrgencyAnalyzer)->analyze('Description.'))->toThrow(UrgencyProviderException::class);
});

test('rejects invalid timeouts before any external request', function (mixed $timeout) {
    Http::preventStrayRequests();
    config(['urgency.timeout' => $timeout]);

    expect(fn () => (new OpenAiUrgencyAnalyzer)->analyze('Description.'))->toThrow(LogicException::class);

    Http::assertNothingSent();
})->with([0, -1, 6, 'invalid', false]);
