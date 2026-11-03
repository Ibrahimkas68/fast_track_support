@props(['name', 'id' => null, 'value' => ''])
@php($value = is_scalar($value) ? (string) $value : '')
<textarea id="{{ $id ?? $name }}" name="{{ $name }}" {{ $attributes->class('form-control min-h-40 resize-y') }}>{{ $value }}</textarea>
