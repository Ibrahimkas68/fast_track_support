@props(['title', 'description'])
<div {{ $attributes->class('px-6 py-16 text-center') }}>
    <h2 class="mb-2 text-lg font-semibold">{{ $title }}</h2>
    <p class="mx-auto mb-6 max-w-md text-sm leading-6 text-muted">{{ $description }}</p>
    {{ $slot }}
</div>
