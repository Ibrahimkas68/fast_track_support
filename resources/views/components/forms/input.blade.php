@props(['name', 'id' => null, 'value' => ''])
@php($value = is_scalar($value) ? (string) $value : '')
<input id="{{ $id ?? $name }}" name="{{ $name }}" value="{{ $value }}" {{ $attributes->class('form-control') }}>
