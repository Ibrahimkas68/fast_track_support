@props(['variant' => 'info', 'title' => null])
@php
    $classes = match ($variant) {
        'error' => 'border-red-200 bg-red-50 text-red-900',
        'success' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        default => 'border-blue-200 bg-blue-50 text-blue-900',
    };
@endphp
<div {{ $attributes->class('rounded-xl border p-4 text-sm '.$classes)->merge(['role' => $variant === 'error' ? 'alert' : 'status']) }}>
    @if($title)<p class="mb-1 font-semibold">{{ $title }}</p>@endif
    {{ $slot }}
</div>
