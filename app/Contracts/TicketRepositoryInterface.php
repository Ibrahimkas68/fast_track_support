<?php

namespace App\Contracts;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;

interface TicketRepositoryInterface
{
    /**
     * @param array{
     *     title: string,
     *     description: string,
     *     priority: TicketPriority,
     *     status: TicketStatus,
     *     suggested_priority: TicketPriority,
     *     analysis_reason: string,
     *     analysis_source: 'llm'|'local'
     * } $data
     */
    public function create(array $data): Ticket;

    public function find(int $id): ?Ticket;

    /** @return LengthAwarePaginator<int, Ticket> */
    public function paginate(
        ?TicketStatus $status = null,
        ?TicketPriority $priority = null,
        int $page = 1,
    ): LengthAwarePaginator;
}
