<?php

use App\Contracts\TicketRepositoryInterface;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Repositories\EloquentTicketRepository;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('creates a ticket through the contract using only explicit writable fields', function () {
    $repository = new EloquentTicketRepository;
    $data = [
        'title' => 'Facturation indisponible',
        'description' => 'Le service est indisponible.',
        'priority' => TicketPriority::Low,
        'status' => TicketStatus::Open,
        'suggested_priority' => TicketPriority::High,
        'analysis_reason' => 'Le service est indisponible.',
        'analysis_source' => 'local',
        'id' => 9999,
        'created_at' => '2000-01-01 00:00:00',
        'reference' => 'FORGED',
    ];

    $ticket = $repository->create($data);

    expect($repository)->toBeInstanceOf(TicketRepositoryInterface::class);
    $this->assertModelExists($ticket);
    $this->assertDatabaseCount('tickets', 1);
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id,
        'title' => 'Facturation indisponible',
        'description' => 'Le service est indisponible.',
        'priority' => 'low',
        'status' => 'open',
        'suggested_priority' => 'high',
        'analysis_reason' => 'Le service est indisponible.',
        'analysis_source' => 'local',
    ]);
    expect($ticket->id)->not->toBe(9999);
    expect($ticket->created_at->format('Y-m-d'))->not->toBe('2000-01-01');
    expect($ticket->reference)->not->toBe('FORGED');
});

test('finds the requested ticket without changing its recommendation', function () {
    $ticket = Ticket::factory()->create([
        'priority' => TicketPriority::Low,
        'suggested_priority' => TicketPriority::High,
    ]);
    $repository = new EloquentTicketRepository;

    $found = $repository->find($ticket->id);

    expect($found->id)->toBe($ticket->id);
    expect($found->priority)->toBe(TicketPriority::Low);
    expect($found->suggested_priority)->toBe(TicketPriority::High);
});

test('returns null for a missing ticket', function () {
    $repository = new EloquentTicketRepository;

    $found = $repository->find(9999);

    expect($found)->toBeNull();
});

test('filters independently by status', function (TicketStatus $status) {
    $target = Ticket::factory()->create(['status' => $status]);
    foreach (TicketStatus::cases() as $other) {
        if ($other !== $status) {
            Ticket::factory()->create(['status' => $other]);
        }
    }
    $repository = new EloquentTicketRepository;

    $page = $repository->paginate(status: $status);

    expect($page->total())->toBe(1);
    expect($page->getCollection()->modelKeys())->toBe([$target->id]);
})->with(TicketStatus::cases());

test('filters independently by priority', function (TicketPriority $priority) {
    $target = Ticket::factory()->create(['priority' => $priority]);
    foreach (TicketPriority::cases() as $other) {
        if ($other !== $priority) {
            Ticket::factory()->create(['priority' => $other]);
        }
    }
    $repository = new EloquentTicketRepository;

    $page = $repository->paginate(priority: $priority);

    expect($page->total())->toBe(1);
    expect($page->getCollection()->modelKeys())->toBe([$target->id]);
})->with(TicketPriority::cases());

test('combines status and priority using AND', function (TicketStatus $status, TicketPriority $priority) {
    $target = Ticket::factory()->create(['status' => $status, 'priority' => $priority]);
    $otherStatus = $status === TicketStatus::Open ? TicketStatus::Resolved : TicketStatus::Open;
    $otherPriority = $priority === TicketPriority::High ? TicketPriority::Low : TicketPriority::High;
    Ticket::factory()->create(['status' => $status, 'priority' => $otherPriority]);
    Ticket::factory()->create(['status' => $otherStatus, 'priority' => $priority]);
    $repository = new EloquentTicketRepository;

    $page = $repository->paginate($status, $priority);

    expect($page->total())->toBe(1);
    expect($page->getCollection()->modelKeys())->toBe([$target->id]);
})->with(TicketStatus::cases())->with(TicketPriority::cases());

test('sorts by creation date before the identifier', function () {
    $newest = Ticket::factory()->create(['created_at' => '2026-11-03 12:00:00']);
    $older = Ticket::factory()->create(['created_at' => '2026-11-02 12:00:00']);
    $sameDateHigherId = Ticket::factory()->create(['created_at' => '2026-11-03 12:00:00']);
    $repository = new EloquentTicketRepository;

    $page = $repository->paginate();

    expect($page->getCollection()->modelKeys())->toBe([$sameDateHigherId->id, $newest->id, $older->id]);
});

test('paginates 45 tied records without losing or duplicating tickets', function () {
    Ticket::factory()->count(45)->create(['created_at' => '2026-11-03 12:00:00']);
    $repository = new EloquentTicketRepository;

    $first = $repository->paginate(page: 1);
    $second = $repository->paginate(page: 2);
    $third = $repository->paginate(page: 3);

    expect($first->perPage())->toBe(20);
    expect($first->total())->toBe(45);
    expect($first->lastPage())->toBe(3);
    expect($second->currentPage())->toBe(2);
    expect($first->getCollection()->modelKeys())->toBe(range(45, 26));
    expect($second->getCollection()->modelKeys())->toBe(range(25, 6));
    expect($third->getCollection()->modelKeys())->toBe(range(5, 1));
});

test('preserves only the validated filters in pagination links', function () {
    Ticket::factory()->count(21)->create([
        'status' => TicketStatus::Open,
        'priority' => TicketPriority::High,
    ]);
    request()->query->set('unexpected', 'discard-me');
    $repository = new EloquentTicketRepository;

    $page = $repository->paginate(TicketStatus::Open, TicketPriority::High);
    parse_str(parse_url($page->nextPageUrl(), PHP_URL_QUERY), $query);

    expect($query)->toBe(['status' => 'open', 'priority' => 'high', 'page' => '2']);
});

test('returns an empty page for an empty queue', function () {
    $repository = new EloquentTicketRepository;

    $page = $repository->paginate();

    expect($page->total())->toBe(0);
    expect($page->items())->toBe([]);
});

test('returns no matches when the selected filters exclude every ticket', function () {
    Ticket::factory()->create(['status' => TicketStatus::Resolved]);
    $repository = new EloquentTicketRepository;

    $page = $repository->paginate(status: TicketStatus::Open);

    expect($page->total())->toBe(0);
    expect($page->items())->toBe([]);
});

test('returns an empty out-of-range page while retaining the filtered total', function () {
    Ticket::factory()->create();
    $repository = new EloquentTicketRepository;

    $page = $repository->paginate(page: 2);

    expect($page->total())->toBe(1);
    expect($page->currentPage())->toBe(2);
    expect($page->items())->toBe([]);
});
