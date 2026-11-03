@props(['status'])
@php
    [$classes, $icon] = match ($status) {
        \App\Enums\TicketStatus::Open => ['border-blue-200 bg-blue-50 text-blue-700', '◷'],
        \App\Enums\TicketStatus::InProgress => ['border-violet-200 bg-violet-50 text-violet-700', '◴'],
        \App\Enums\TicketStatus::Resolved => ['border-emerald-200 bg-emerald-50 text-emerald-800', '✓'],
    };
@endphp
<span {{ $attributes->class('inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold '.$classes) }}><span aria-hidden="true">{{ $icon }}</span>{{ $status->label() }}</span>
