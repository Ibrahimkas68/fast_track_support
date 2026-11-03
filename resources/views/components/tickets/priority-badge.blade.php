@props(['priority'])
@php
    [$classes, $icon] = match ($priority) {
        \App\Enums\TicketPriority::High => ['border-red-200 bg-red-50 text-red-700', '⇈'],
        \App\Enums\TicketPriority::Medium => ['border-amber-200 bg-amber-50 text-amber-800', '='],
        \App\Enums\TicketPriority::Low => ['border-slate-200 bg-slate-100 text-slate-700', '⌄'],
    };
@endphp
<span {{ $attributes->class('inline-flex items-center gap-1.5 whitespace-nowrap rounded-full border px-2.5 py-1 text-xs font-semibold '.$classes) }}><span aria-hidden="true">{{ $icon }}</span>{{ $priority->label() }}</span>
