{{-- Logo SAPA: rumah + tiga warga (komunitas) dalam kotak brand. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center justify-center rounded-2xl bg-gradient-to-br from-brand-500 via-brand-600 to-brand-800 text-white shadow-sm']) }}>
    <svg viewBox="0 0 48 48" fill="none" class="w-2/3 h-2/3" aria-hidden="true">
        {{-- Atap rumah --}}
        <path d="M24 9 39 21v1.5a1 1 0 0 1-1.6.8L24 13 10.6 23.3a1 1 0 0 1-1.6-.8V21L24 9Z" fill="currentColor" opacity="0.55"/>
        {{-- Badan rumah --}}
        <path d="M13 22 24 14l11 8v11.5a1.5 1.5 0 0 1-1.5 1.5H14.5A1.5 1.5 0 0 1 13 33.5V22Z" fill="currentColor" opacity="0.28"/>
        {{-- Tiga warga --}}
        <g fill="currentColor">
            <circle cx="16.5" cy="27.5" r="3"/>
            <path d="M11 37c0-3 2.5-5 5.5-5s5.5 2 5.5 5v.5H11V37Z"/>
            <circle cx="31.5" cy="27.5" r="3"/>
            <path d="M26 37c0-3 2.5-5 5.5-5s5.5 2 5.5 5v.5H26V37Z"/>
            <circle cx="24" cy="25" r="3.6"/>
            <path d="M17.5 37c0-3.6 2.9-6 6.5-6s6.5 2.4 6.5 6v.6h-13V37Z"/>
        </g>
    </svg>
</span>
