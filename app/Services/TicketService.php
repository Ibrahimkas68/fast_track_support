<?php

namespace App\Services;

use App\Contracts\TicketRepositoryInterface;
use App\Contracts\UrgencyAnalyzerInterface;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Exceptions\TicketStorageException;
use App\Models\Ticket;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;

class TicketService
{
    public function __construct(
        private TicketRepositoryInterface $tickets,
        private UrgencyAnalyzerInterface $analyzer,
    ) {}

    /** @param array{title: string, description: string, priority: TicketPriority, status: TicketStatus} $data */
    public function create(array $data): Ticket
    {
        $result = $this->analyzer->analyze($data['description']);

        try {
            return $this->tickets->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'priority' => $data['priority'],
                'status' => $data['status'],
                'suggested_priority' => $result->priority,
                'analysis_reason' => $result->reason,
                'analysis_source' => $result->source,
            ]);
        } catch (QueryException) {
            throw new TicketStorageException('Impossible d’enregistrer le ticket. Veuillez réessayer.');
        }
    }

    /** @return LengthAwarePaginator<int, Ticket> */
    public function paginate(?TicketStatus $status = null, ?TicketPriority $priority = null, int $page = 1): LengthAwarePaginator
    {
        return $this->tickets->paginate($status, $priority, $page);
    }

    public function find(int $id): ?Ticket
    {
        return $this->tickets->find($id);
    }
}
