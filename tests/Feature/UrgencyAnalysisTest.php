<?php

use App\Contracts\UrgencyAnalyzerInterface;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    config(['urgency.driver' => 'openai', 'urgency.api_key' => 'test-key', 'urgency.rate_limit' => 100]);
});

test('persists the real adapter result through the creation pipeline', function () {
    Http::preventStrayRequests();
    Http::fake(['https://api.openai.com/v1/responses' => Http::response(json_decode(file_get_contents(__DIR__.'/../Fixtures/openai-completed.json'), true))]);

    $response = $this->post('/tickets', ['title' => 'Demande', 'description' => 'Service indisponible.', 'priority' => 'low', 'status' => 'open']);

    $response->assertStatus(303);
    $ticket = Ticket::query()->sole();
    $this->assertDatabaseHas('tickets', ['priority' => 'low', 'suggested_priority' => 'high', 'analysis_source' => 'llm']);
    $this->get('/tickets/'.$ticket->id)->assertOk()->assertSee('Analyse par modèle de langage');
    Http::assertSentCount(1);
});

test('creates with local fallback when the provider is unavailable', function (string $failure) {
    Http::preventStrayRequests();
    $response = match ($failure) {
        'timeout' => Http::failedConnection(),
        'malformed' => Http::response(['status' => 'completed', 'output' => []]),
        default => Http::response([], (int) $failure),
    };
    Http::fake(['https://api.openai.com/v1/responses' => $response]);
    $start = hrtime(true);

    $this->post('/tickets', ['title' => 'Demande', 'description' => 'Le service est indisponible.', 'priority' => 'low', 'status' => 'open'])->assertStatus(303);

    expect((hrtime(true) - $start) / 1e9)->toBeLessThan(10);
    $ticket = Ticket::query()->sole();
    $this->assertDatabaseHas('tickets', ['priority' => 'low', 'suggested_priority' => 'high', 'analysis_source' => 'local']);
    $this->get('/tickets/'.$ticket->id)->assertOk()->assertSee('Analyse locale de secours');
})->with(['timeout', 'malformed', '401', '403', '429', '500', '503']);

test('works locally without any external call when disabled or unconfigured', function (string $driver, ?string $key) {
    Http::preventStrayRequests();
    config(['urgency.driver' => $driver, 'urgency.api_key' => $key]);

    $this->post('/tickets', ['title' => 'Demande', 'description' => 'Simple défaut visuel.', 'priority' => 'high', 'status' => 'open'])->assertStatus(303);

    $this->assertDatabaseHas('tickets', ['priority' => 'high', 'suggested_priority' => 'low', 'analysis_source' => 'local']);
    Http::assertNothingSent();
})->with([['local', 'test-key'], ['openai', null], ['openai', '']]);

test('logs only controlled metadata without descriptions keys or raw responses', function () {
    Http::preventStrayRequests();
    Http::fake(['https://api.openai.com/v1/responses' => Http::response(['error' => 'SENSITIVE-PROVIDER'], 500)]);
    Log::spy();

    $this->post('/tickets', ['title' => 'SENSITIVE-TITLE', 'description' => 'SENSITIVE-DESCRIPTION', 'priority' => 'low', 'status' => 'open'])->assertStatus(303);

    Log::shouldHaveReceived('info')->with('Urgency analysis completed', Mockery::on(function (array $context): bool {
        expect(array_keys($context))->toBe(['source', 'duration_ms', 'failure_category']);
        expect($context['source'])->toBe('local');
        expect($context['failure_category'])->toBe('http');

        return ! str_contains(json_encode($context), 'SENSITIVE') && ! str_contains(json_encode($context), 'test-key');
    }))->once();
});

test('renders provider explanations as escaped text', function () {
    Http::preventStrayRequests();
    $fixture = json_decode(file_get_contents(__DIR__.'/../Fixtures/openai-completed.json'), true);
    $fixture['output'][0]['content'][0]['text'] = json_encode(['priority' => 'high', 'reason' => '<script>alert("provider")</script>']);
    Http::fake(['https://api.openai.com/v1/responses' => Http::response($fixture)]);

    $this->post('/tickets', ['title' => 'Demande', 'description' => 'Service indisponible.', 'priority' => 'low', 'status' => 'open'])->assertStatus(303);
    $ticket = Ticket::query()->sole();

    $this->get('/tickets/'.$ticket->id)->assertOk()->assertSee('<script>alert("provider")</script>')
        ->assertDontSee('<script>alert("provider")</script>', false);
    Http::assertSentCount(1);
});

test('rejects an unknown driver without calling a provider', function () {
    Http::preventStrayRequests();
    config(['urgency.driver' => 'unknown']);

    expect(fn () => app(UrgencyAnalyzerInterface::class)->analyze('Description.'))
        ->toThrow(LogicException::class);

    Http::assertNothingSent();
});
