<?php

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('shows the queue and the empty creation action', function () {
    $this->get('/tickets')->assertOk()->assertSee('Aucun ticket pour le moment')
        ->assertSee(route('tickets.create'));
});

test('shows the correct ticket for every combined filter', function (TicketStatus $status, TicketPriority $priority) {
    $target = Ticket::factory()->create(['title' => 'Ticket attendu', 'status' => $status, 'priority' => $priority]);
    Ticket::factory()->create([
        'title' => 'Ticket exclu',
        'status' => $status === TicketStatus::Open ? TicketStatus::Resolved : TicketStatus::Open,
        'priority' => $priority,
    ]);

    $response = $this->get('/tickets?'.http_build_query(['status' => $status->value, 'priority' => $priority->value]));

    $response->assertOk()->assertSee('Ticket attendu')->assertDontSee('Ticket exclu')
        ->assertSee(route('tickets.show', $target->id))->assertSee('1 ticket');
})->with(TicketStatus::cases())->with(TicketPriority::cases());

test('shows a distinct empty filtered state and a reset link', function () {
    Ticket::factory()->create(['status' => TicketStatus::Resolved]);

    $this->get('/tickets?status=open')->assertOk()->assertSee('Aucun résultat')
        ->assertSee('Réinitialiser les filtres')->assertSee(route('tickets.index'));
});

test('rejects invalid query parameters with a safe 422 page', function (string $query) {
    $this->get('/tickets?'.$query)->assertUnprocessable()->assertSee('Paramètres invalides');
})->with(['status=unknown', 'priority=urgent', 'page=0', 'page=-1', 'page=abc', 'status[]=open', 'priority[]=high']);

test('paginates the queue in stable newest-first order and retains valid filters', function () {
    Ticket::factory()->count(45)->create([
        'status' => TicketStatus::Open, 'priority' => TicketPriority::High,
        'created_at' => '2026-11-03 10:00:00',
    ]);

    $response = $this->get('/tickets?status=open&priority=high&page=2&unexpected=discard-me');

    $response->assertOk()->assertSee('FT-0025')->assertSee('FT-0006')
        ->assertDontSee('FT-0045')->assertDontSee('discard-me')->assertSee('Précédent')->assertSee('Suivant')->assertSee('Page 2 sur 3');
    expect($response->viewData('tickets')->currentPage())->toBe(2);
    expect($response->viewData('tickets')->total())->toBe(45);
    parse_str(parse_url($response->viewData('tickets')->nextPageUrl(), PHP_URL_QUERY), $query);
    expect($query)->toBe(['status' => 'open', 'priority' => 'high', 'page' => '3']);
});

test('keeps an out-of-range page understandable and links to the first page', function () {
    Ticket::factory()->create();

    $this->get('/tickets?page=99')->assertOk()->assertSee('Aucun résultat')
        ->assertSee('Revenir à la première page');
});
