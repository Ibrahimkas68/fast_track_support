<?php

use App\Contracts\TicketRepositoryInterface;
use App\Contracts\UrgencyAnalyzerInterface;
use App\Data\UrgencyResult;
use App\Enums\TicketPriority;
use App\Models\Ticket;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    config(['urgency.driver' => 'local', 'urgency.rate_limit' => 100]);
});

function validTicketInput(array $overrides = []): array
{
    return array_replace([
        'title' => 'Accès au service',
        'description' => 'Le service est indisponible.',
        'priority' => 'low',
        'status' => 'open',
    ], $overrides);
}

test('renders the French form with medium and open defaults', function () {
    $this->get('/tickets/create')->assertOk()->assertSee('Créer un ticket')
        ->assertSee('value="medium" selected', false)->assertSee('value="open" selected', false);
});

test('creates and reloads a ticket with local analysis and trimmed fields', function () {
    $response = $this->post('/tickets', validTicketInput(['title' => '  Accès au service  ']));

    $ticket = Ticket::query()->sole();
    $response->assertStatus(303)->assertRedirect(route('tickets.show', $ticket->id))
        ->assertSessionHas('success');
    $this->assertDatabaseHas('tickets', [
        'title' => 'Accès au service', 'priority' => 'low',
        'suggested_priority' => 'high', 'analysis_source' => 'local',
    ]);
    $this->get($response->headers->get('Location'))->assertOk()->assertSee($ticket->reference)
        ->assertSee('Accès au service')->assertSee('Analyse locale de secours');
    $this->get($response->headers->get('Location'))->assertOk()->assertSee('Haute')->assertSee('Basse');
});

test('refuses invalid input before invoking analysis and preserves old input', function (string $field, mixed $value) {
    $analyzer = Mockery::mock(UrgencyAnalyzerInterface::class);
    $analyzer->shouldNotReceive('analyze');
    $this->app->instance(UrgencyAnalyzerInterface::class, $analyzer);
    $data = validTicketInput([$field => $value]);

    $response = $this->from('/tickets/create')->post('/tickets', $data);

    $response->assertRedirect('/tickets/create')->assertSessionHasErrors($field)
        ->assertSessionHas('_old_input');
    $this->assertDatabaseCount('tickets', 0);
})->with([
    ['title', ''], ['title', '   '], ['title', str_repeat('é', 161)], ['title', ['array']],
    ['description', ''], ['description', '   '], ['description', str_repeat('é', 5001)], ['description', ['array']],
    ['priority', 'critical'], ['priority', null], ['status', 'closed'], ['status', null],
    ['suggested_priority', 'high'], ['analysis_reason', 'forged'], ['analysis_source', 'llm'],
]);

test('shows a French validation error with the previous values', function () {
    $response = $this->from('/tickets/create')->post('/tickets', validTicketInput(['title' => '']));

    $response->assertSessionHasErrors(['title' => 'Le titre est obligatoire.']);
    $this->withCookie(session()->getName(), session()->getId());
    $page = $this->get('/tickets/create');
    $page->assertOk()->assertSee('Le titre est obligatoire.')->assertSee('Le service est indisponible.');
});

test('accepts the exact title and description length boundaries', function (int $titleLength, int $descriptionLength) {
    $response = $this->post('/tickets', validTicketInput([
        'title' => str_repeat('é', $titleLength), 'description' => str_repeat('é', $descriptionLength),
    ]));

    $response->assertStatus(303);
    $this->assertDatabaseCount('tickets', 1);
})->with([[1, 1], [160, 5000]]);

test('preserves a controlled external recommendation without changing the chosen priority', function () {
    $analyzer = Mockery::mock(UrgencyAnalyzerInterface::class);
    $analyzer->shouldReceive('analyze')->once()->with('Le service est indisponible.')
        ->andReturn(new UrgencyResult(TicketPriority::High, 'Service indisponible.', 'llm'));
    $this->app->instance(UrgencyAnalyzerInterface::class, $analyzer);

    $this->post('/tickets', validTicketInput())->assertStatus(303);

    $this->assertDatabaseHas('tickets', ['priority' => 'low', 'suggested_priority' => 'high', 'analysis_source' => 'llm']);
});

test('returns a recoverable error when persistence fails without announcing success', function () {
    $repository = Mockery::mock(TicketRepositoryInterface::class);
    $repository->shouldReceive('create')->once()->andThrow(new QueryException(
        'sqlite', 'insert into tickets', ['SENSITIVE-BINDING'], new PDOException('SENSITIVE-DATABASE'),
    ));
    $this->app->instance(TicketRepositoryInterface::class, $repository);

    $response = $this->post('/tickets', validTicketInput());

    $response->assertRedirect('/tickets/create')->assertSessionHasErrors('storage')
        ->assertSessionMissing('success')->assertSessionHasInput('title', 'Accès au service');
    $this->assertDatabaseCount('tickets', 0);
    $this->withCookie(session()->getName(), session()->getId());
    $this->get('/tickets/create')->assertOk()->assertSee('Impossible d’enregistrer le ticket')
        ->assertDontSee('SENSITIVE-BINDING')->assertDontSee('SENSITIVE-DATABASE');
});

test('renders a safe 404 for a missing or invalid ticket identifier', function (string $identifier) {
    $this->get('/tickets/'.$identifier)->assertNotFound()->assertSee('Page introuvable');
})->with(['99999', 'not-a-number', '-1']);
