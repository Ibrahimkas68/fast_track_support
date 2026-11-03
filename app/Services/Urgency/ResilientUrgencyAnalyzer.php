<?php

namespace App\Services\Urgency;

use App\Contracts\UrgencyAnalyzerInterface;
use App\Data\UrgencyResult;
use App\Exceptions\UrgencyProviderException;
use Illuminate\Support\Facades\Log;
use LogicException;

class ResilientUrgencyAnalyzer implements UrgencyAnalyzerInterface
{
    public function __construct(
        private OpenAiUrgencyAnalyzer $external,
        private RuleBasedUrgencyAnalyzer $local,
    ) {}

    public function analyze(string $description): UrgencyResult
    {
        $start = hrtime(true);
        $driver = config('urgency.driver');
        $key = config('urgency.api_key');
        $failure = null;

        if (! in_array($driver, ['local', 'openai'], true)) {
            throw new LogicException('URGENCY_DRIVER doit être local ou openai.');
        }

        if ($driver === 'openai' && is_string($key) && trim($key) !== '') {
            try {
                $result = $this->external->analyze($description);
            } catch (UrgencyProviderException $exception) {
                $failure = $exception->category;
                $result = $this->local->analyze($description);
            }
        } else {
            $failure = $driver === 'openai' ? 'not_configured' : null;
            $result = $this->local->analyze($description);
        }

        Log::info('Urgency analysis completed', [
            'source' => $result->source,
            'duration_ms' => round((hrtime(true) - $start) / 1e6, 2),
            'failure_category' => $failure,
        ]);

        return $result;
    }
}
