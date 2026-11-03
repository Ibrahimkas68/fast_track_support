@props(['variant' => 'primary', 'type' => 'button', 'href' => null])
@php
    $style = $variant === 'primary'
        ? 'bg-brand text-white hover:bg-blue-800 border-transparent'
        : 'bg-white text-muted hover:bg-slate-50 border-line';
    $classes = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-semibold disabled:cursor-wait disabled:opacity-60 '.$style;
@endphp
@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
