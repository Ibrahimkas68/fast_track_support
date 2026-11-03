<?php

namespace App\Providers;

use App\Contracts\TicketRepositoryInterface;
use App\Contracts\UrgencyAnalyzerInterface;
use App\Repositories\EloquentTicketRepository;
use App\Services\Urgency\ResilientUrgencyAnalyzer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use LogicException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TicketRepositoryInterface::class, EloquentTicketRepository::class);
        $this->app->bind(UrgencyAnalyzerInterface::class, ResilientUrgencyAnalyzer::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('ticket-creation', function (Request $request): Limit {
            $configured = config('urgency.rate_limit');
            $limit = is_int($configured) || is_string($configured)
                ? filter_var($configured, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
                : false;

            if ($limit === false) {
                throw new LogicException('TICKET_RATE_LIMIT doit être un entier strictement positif.');
            }

            return Limit::perMinute($limit)->by('ticket-creation:'.$request->ip());
        });
    }
}
