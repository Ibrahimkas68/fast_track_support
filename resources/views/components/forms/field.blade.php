@props(['name', 'label', 'help' => null, 'error' => null])
<div {{ $attributes->class('space-y-2') }}>
    <label for="{{ $name }}" class="block text-sm font-semibold">{{ $label }}</label>
    {{ $slot }}
    @if($help)<p id="{{ $name }}-help" class="text-sm leading-5 text-muted">{{ $help }}</p>@endif
    @if($error)<p id="{{ $name }}-error" class="text-sm font-medium text-red-700" role="alert">{{ $error }}</p>@endif
</div>
