<?php

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'priority' => TicketPriority::Medium->value,
            'status' => TicketStatus::Open->value,
            'suggested_priority' => TicketPriority::Medium->value,
            'analysis_reason' => 'Donnée fictive : aucun signal déterminant reconnu.',
            'analysis_source' => 'local',
        ];
    }
}
