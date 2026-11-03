<?php

use App\Contracts\TicketRepositoryInterface;
use App\Repositories\EloquentTicketRepository;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

test('resolves the ticket repository contract', function () {
    expect(app(TicketRepositoryInterface::class))->toBeInstanceOf(EloquentTicketRepository::class);
});

test('renders safe French HTML errors even with debug enabled', function (int $status, string $message) {
    config(['app.debug' => true]);
    Route::get('/_test/error', fn () => abort($status, 'SENSITIVE-DIAGNOSTIC'));

    $response = $this->get('/_test/error', ['Accept' => 'text/html']);

    $response->assertStatus($status)->assertSee($message)->assertDontSee('SENSITIVE-DIAGNOSTIC');
})->with([
    [404, 'Page introuvable'],
    [419, 'Formulaire expiré'],
    [422, 'Paramètres invalides'],
    [429, 'Trop de demandes'],
]);

test('preserves safe JSON errors when JSON is requested', function () {
    config(['app.debug' => true]);
    Route::get('/_test/error', fn () => abort(422, 'SENSITIVE-DIAGNOSTIC'));

    $response = $this->getJson('/_test/error');

    $response->assertUnprocessable()->assertExactJson(['message' => 'Paramètres invalides']);
});

test('uses JSON for an API-shaped path without an Accept header', function () {
    Route::get('/api/_test/error', fn () => abort(404, 'SENSITIVE-DIAGNOSTIC'));

    $response = $this->get('/api/_test/error');

    $response->assertNotFound()->assertExactJson(['message' => 'Page introuvable']);
});

test('limits creation attempts per IP and preserves retry headers', function () {
    config(['urgency.rate_limit' => 2]);
    $this->freezeTime();
    Route::post('/_test/limit', fn () => response('ok'))->middleware('throttle:ticket-creation');

    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.61']);
    $this->post('/_test/limit')->assertOk();
    $this->post('/_test/limit')->assertOk();
    $this->post('/_test/limit')->assertTooManyRequests()->assertHeader('Retry-After')
        ->assertHeader('X-RateLimit-Limit', '2')->assertSee('Trop de demandes');
    $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.62']);
    $this->post('/_test/limit')->assertOk();
});

test('rejects a non-positive or non-integer creation limit', function (mixed $value) {
    config(['urgency.rate_limit' => $value]);
    $limiter = app(RateLimiter::class)->limiter('ticket-creation');

    expect(fn () => $limiter(Request::create('/tickets', 'POST')))
        ->toThrow(LogicException::class);
})->with([0, -1, 'invalid', '2.5', false]);
