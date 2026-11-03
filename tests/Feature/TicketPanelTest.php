<?php

use App\Models\Ticket;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('returns a form fragment with CSRF for progressive enhancement', function () {
    $this->get('/tickets/create?panel=1')->assertOk()->assertSee('data-ticket-form', false)
        ->assertSee('name="_token"', false)->assertDontSee('<html', false)->assertDontSee('<header', false);
    $this->get('/tickets/create')->assertOk()->assertSee('<html lang="fr">', false);
});

test('returns the same ticket detail as a fragment and keeps missing tickets safe', function () {
    $ticket = Ticket::factory()->create(['title' => 'Titre partagé']);

    $this->get('/tickets/'.$ticket->id.'?panel=1')->assertOk()->assertSee('Titre partagé')
        ->assertSee('Urgence recommandée')->assertDontSee('<html', false);
    $this->get('/tickets/9999?panel=1')->assertNotFound()->assertSee('Page introuvable');
});
