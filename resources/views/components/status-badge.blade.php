@props(['status'])

{{-- Membungkus enum status (HasLabel + HasColor) jadi badge konsisten. --}}
@php
    $color = method_exists($status, 'getColor') ? $status->getColor() : 'gray';
    $label = method_exists($status, 'getLabel') ? $status->getLabel() : (string) $status->value;
@endphp

<x-ui.badge :color="$color">{{ $label }}</x-ui.badge>
