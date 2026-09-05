@props(['months' => []])

@php
    $max = collect($months)->flatMap(fn ($m) => [$m['income'], $m['expense']])->max() ?: 1;
@endphp

<div>
    <div class="flex items-end justify-between gap-2 sm:gap-4 h-44">
        @foreach ($months as $m)
            <div class="flex-1 flex flex-col items-center justify-end gap-1 h-full group">
                <div class="w-full flex items-end justify-center gap-1 h-full">
                    <div class="w-1/2 max-w-[18px] rounded-t-md bg-emerald-400 transition-all group-hover:bg-emerald-500"
                         style="height: {{ max(2, round($m['income'] / $max * 100)) }}%"
                         title="Pemasukan: {{ rupiah($m['income']) }}"></div>
                    <div class="w-1/2 max-w-[18px] rounded-t-md bg-red-300 transition-all group-hover:bg-red-400"
                         style="height: {{ max(2, round($m['expense'] / $max * 100)) }}%"
                         title="Pengeluaran: {{ rupiah($m['expense']) }}"></div>
                </div>
                <span class="text-[11px] text-slate-400">{{ $m['label'] }}</span>
            </div>
        @endforeach
    </div>
    <div class="flex items-center justify-center gap-5 mt-4 text-xs text-slate-500">
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-400"></span> Pemasukan</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-red-300"></span> Pengeluaran</span>
    </div>
</div>
