@props(['padding' => 'p-5 sm:p-6'])

<div {{ $attributes->merge(['class' => 'card-surface ' . $padding]) }}>
    {{ $slot }}
</div>
