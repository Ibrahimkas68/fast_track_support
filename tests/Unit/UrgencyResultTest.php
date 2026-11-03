<?php

use App\Data\UrgencyResult;
use App\Enums\TicketPriority;

test('preserves the recommended priority and analysis source', function (string $source) {
    $result = new UrgencyResult(TicketPriority::High, 'Le service est indisponible.', $source);

    expect($result->priority)->toBe(TicketPriority::High);
    expect($result->reason)->toBe('Le service est indisponible.');
    expect($result->source)->toBe($source);
})->with(['local', 'llm']);

test('trims the explanation before storing it', function () {
    $result = new UrgencyResult(TicketPriority::Low, " \n Défaut visuel. \t", 'local');

    expect($result->reason)->toBe('Défaut visuel.');
});

test('accepts a one-character explanation', function () {
    $result = new UrgencyResult(TicketPriority::Medium, 'é', 'local');

    expect($result->reason)->toBe('é');
});

test('accepts 500 Unicode characters after trimming', function () {
    $reason = str_repeat('é', 500);

    $result = new UrgencyResult(TicketPriority::Medium, ' '.$reason.' ', 'local');

    expect($result->reason)->toBe($reason);
});

test('rejects an empty or oversized explanation', function (string $reason) {
    expect(fn () => new UrgencyResult(TicketPriority::Medium, $reason, 'local'))
        ->toThrow(InvalidArgumentException::class);
})->with([
    'empty' => '',
    'whitespace' => " \t\n ",
    '501 Unicode characters' => str_repeat('é', 501),
]);

test('rejects an unsupported analysis source', function (string $source) {
    expect(fn () => new UrgencyResult(TicketPriority::Medium, 'Impact non déterminé.', $source))
        ->toThrow(InvalidArgumentException::class);
})->with(['', 'unknown', 'LLM']);
