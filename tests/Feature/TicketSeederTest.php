<?php

use App\Models\Ticket;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

test('seeds nine repeatable fictional combinations without contacting a provider', function () {
    Http::preventStrayRequests();

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('tickets', 9);
    expect(Ticket::query()->get()->map(fn (Ticket $ticket) => $ticket->status->value.':'.$ticket->priority->value)->unique()->count())->toBe(9);
    expect(Ticket::query()->where('analysis_source', 'llm')->count())->toBeGreaterThan(0);
    expect(Ticket::query()->where('analysis_source', 'local')->count())->toBeGreaterThan(0);
    expect(Ticket::query()->where('analysis_source', 'llm')->where('analysis_reason', 'not like', 'Exemple fictif%')->count())->toBe(0);
    Http::assertNothingSent();
});
