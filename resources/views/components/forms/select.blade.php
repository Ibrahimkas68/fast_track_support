@props(['name', 'id' => null, 'value' => '', 'options' => []])
@php($value = is_scalar($value) ? (string) $value : '')
<select id="{{ $id ?? $name }}" name="{{ $name }}" {{ $attributes->class('form-control') }}>
    @foreach($options as $key => $label)<option value="{{ $key }}" @selected((string) $value === (string) $key)>{{ $label }}</option>@endforeach
</select>
