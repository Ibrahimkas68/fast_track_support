<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'title',
    'description',
    'priority',
    'status',
    'suggested_priority',
    'analysis_reason',
    'analysis_source',
])]
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'suggested_priority' => TicketPriority::class,
        ];
    }

    /** @return Attribute<string|null, never> */
    protected function reference(): Attribute
    {
        return Attribute::make(
            get: fn (): ?string => $this->getKey() === null
                ? null
                : 'FT-'.str_pad((string) $this->getKey(), 4, '0', STR_PAD_LEFT),
        );
    }
}
