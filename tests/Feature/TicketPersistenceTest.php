<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

test('persists the request and recommendation without changing the human priority', function () {
    $ticket = Ticket::factory()->create([
        'title' => 'Accès à la facturation indisponible',
        'description' => 'Le service de facturation est indisponible.',
        'priority' => TicketPriority::Low,
        'status' => TicketStatus::InProgress,
        'suggested_priority' => TicketPriority::High,
        'analysis_reason' => 'Service indisponible.',
        'analysis_source' => 'llm',
    ]);

    $saved = $ticket->fresh();

    expect($saved->title)->toBe('Accès à la facturation indisponible');
    expect($saved->description)->toBe('Le service de facturation est indisponible.');
    expect($saved->priority)->toBe(TicketPriority::Low);
    expect($saved->status)->toBe(TicketStatus::InProgress);
    expect($saved->suggested_priority)->toBe(TicketPriority::High);
    expect($saved->analysis_reason)->toBe('Service indisponible.');
    expect($saved->analysis_source)->toBe('llm');
});

test('creates a fictional local recommendation by default', function () {
    $ticket = Ticket::factory()->create();

    $saved = $ticket->fresh();

    expect($saved->priority)->toBe(TicketPriority::Medium);
    expect($saved->status)->toBe(TicketStatus::Open);
    expect($saved->suggested_priority)->toBe(TicketPriority::Medium);
    expect($saved->analysis_source)->toBe('local');
    expect($saved->analysis_reason)->not->toBeEmpty();
});

test('derives a reference with at least four digits from the persisted identifier', function (int $id, string $reference) {
    $ticket = Ticket::factory()->create(['id' => $id]);

    $saved = $ticket->fresh();

    expect($saved->reference)->toBe($reference);
    expect(array_key_exists('reference', $saved->getAttributes()))->toBeFalse();
})->with([
    'first ticket' => [1, 'FT-0001'],
    'four digits' => [9999, 'FT-9999'],
    'five digits' => [10000, 'FT-10000'],
]);

test('rejects unsupported persisted enum values', function (string $field) {
    $attributes = Ticket::factory()->raw();
    $attributes[$field] = 'unknown';

    expect(fn () => DB::table('tickets')->insert($attributes))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('tickets', 0);
})->with(['priority', 'status', 'suggested_priority', 'analysis_source']);

test('does not persist a ticket with an incomplete recommendation', function () {
    $attributes = Ticket::factory()->raw();
    unset($attributes['analysis_reason']);

    expect(fn () => DB::table('tickets')->insert($attributes))
        ->toThrow(QueryException::class);

    $this->assertDatabaseCount('tickets', 0);
});
