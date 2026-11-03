<?php

use App\Models\Ticket;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(LazilyRefreshDatabase::class);

test('escapes ticket text in the queue and detail including hostile attribute input', function () {
    $ticket = Ticket::factory()->create([
        'title' => '<img src=x onerror=alert(1)>',
        'description' => '<script>alert("ticket")</script>',
    ]);

    $this->get('/tickets')->assertOk()->assertSee('<img src=x onerror=alert(1)>')
        ->assertDontSee('<img src=x onerror=alert(1)>', false)->assertDontSee('<script>alert("ticket")</script>', false);
    $this->get('/tickets/'.$ticket->id)->assertOk()->assertSee('<script>alert("ticket")</script>')
        ->assertDontSee('<script>alert("ticket")</script>', false);
});

test('rate limits actual creation before external analysis and leaves the extra ticket uncreated', function () {
    config(['urgency.rate_limit' => 1, 'urgency.driver' => 'openai', 'urgency.api_key' => 'test-key']);
    Http::preventStrayRequests();
    Http::fake(['https://api.openai.com/v1/responses' => Http::response(json_decode(file_get_contents(__DIR__.'/../Fixtures/openai-completed.json'), true))]);
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.71']);
    $data = ['title' => 'Demande', 'description' => 'Service indisponible.', 'priority' => 'low', 'status' => 'open'];

    $this->post('/tickets', $data)->assertStatus(303);
    $this->post('/tickets', $data)->assertTooManyRequests()->assertHeader('Retry-After');

    $this->assertDatabaseCount('tickets', 1);
    Http::assertSentCount(1);
});

test('rejects malformed input without calling the external service', function () {
    config(['urgency.driver' => 'openai', 'urgency.api_key' => 'test-key']);
    Http::preventStrayRequests();

    $this->post('/tickets', ['title' => '   ', 'description' => 'Test', 'priority' => 'unknown', 'status' => 'open'])
        ->assertRedirect('/tickets/create')->assertSessionHasErrors(['title', 'priority']);

    $this->assertDatabaseCount('tickets', 0);
    Http::assertNothingSent();
});

test('keeps array-valued old input recoverable without rendering a server error', function () {
    $response = $this->post('/tickets', ['title' => ['bad'], 'description' => 'Texte conservé', 'priority' => ['bad'], 'status' => 'open']);
    $response->assertRedirect('/tickets/create')->assertSessionHasErrors(['title', 'priority']);
    $this->withCookie(session()->getName(), session()->getId());

    $this->get('/tickets/create')->assertOk()->assertSee('Texte conservé')->assertSee('Le titre doit être un texte.');
});
