<?php

namespace App\Data;

use App\Enums\TicketPriority;
use InvalidArgumentException;

final readonly class UrgencyResult
{
    public string $reason;

    public function __construct(
        public TicketPriority $priority,
        string $reason,
        public string $source,
    ) {
        $reason = trim($reason);

        if ($reason === '' || mb_strlen($reason, 'UTF-8') > 500) {
            throw new InvalidArgumentException('Le motif doit contenir entre 1 et 500 caractères.');
        }

        if (! in_array($source, ['llm', 'local'], true)) {
            throw new InvalidArgumentException('La source doit être llm ou local.');
        }

        $this->reason = $reason;
    }
}
