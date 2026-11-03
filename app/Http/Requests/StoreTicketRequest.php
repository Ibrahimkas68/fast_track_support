<?php

namespace App\Http\Requests;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    protected $redirect = '/tickets/create';

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        foreach (['title', 'description'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => trim($this->input($field))]);
            }
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:5000'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'suggested_priority' => ['prohibited'],
            'analysis_reason' => ['prohibited'],
            'analysis_source' => ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.string' => 'Le titre doit être un texte.',
            'title.max' => 'Le titre ne doit pas dépasser 160 caractères.',
            'description.required' => 'La description est obligatoire.',
            'description.string' => 'La description doit être un texte.',
            'description.max' => 'La description ne doit pas dépasser 5 000 caractères.',
            'priority.required' => 'Choisissez une priorité.',
            'priority.enum' => 'Choisissez une priorité valide.',
            'status.required' => 'Choisissez un statut.',
            'status.enum' => 'Choisissez un statut valide.',
            '*.prohibited' => 'Ce champ est calculé par le serveur.',
        ];
    }
}
