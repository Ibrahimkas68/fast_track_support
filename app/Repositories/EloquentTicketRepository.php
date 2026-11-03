<?php

namespace App\Repositories;

use App\Contracts\TicketRepositoryInterface;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentTicketRepository implements TicketRepositoryInterface
{
    public function create(array $data): Ticket
    {
        return Ticket::query()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'],
            'status' => $data['status'],
            'suggested_priority' => $data['suggested_priority'],
            'analysis_reason' => $data['analysis_reason'],
            'analysis_source' => $data['analysis_source'],
        ]);
    }

    public function find(int $id): ?Ticket
    {
        return Ticket::query()->find($id);
    }

    /** @return LengthAwarePaginator<int, Ticket> */
    public function paginate(
        ?TicketStatus $status = null,
        ?TicketPriority $priority = null,
        int $page = 1,
    ): LengthAwarePaginator {
        $query = Ticket::query();

        if ($status !== null) {
            $query->where('status', $status->value);
        }

        if ($priority !== null) {
            $query->where('priority', $priority->value);
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(perPage: 20, page: $page)
            ->appends(array_filter([
                'status' => $status?->value,
                'priority' => $priority?->value,
            ], static fn (?string $value): bool => $value !== null));
    }
}
