<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center"><x-ui.icon name="chart" class="w-6 h-6" /></span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Kas Transparan</h1>
                <p class="text-sm text-slate-500">Semua pemasukan &amp; pengeluaran RT, terbuka untuk warga.</p>
            </div>
        </div>
    </x-slot>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl bg-gradient-to-br from-brand-600 to-brand-800 text-white p-5 shadow-soft">
            <p class="text-brand-100 text-sm">Saldo Kas Saat Ini</p>
            <p class="text-3xl font-extrabold mt-1">{{ rupiah($balance) }}</p>
        </div>
        <x-ui.stat label="Total Pemasukan" :value="rupiah($income)" icon="trend-up" tone="emerald" />
        <x-ui.stat label="Total Pengeluaran" :value="rupiah($expense)" icon="wallet" tone="red" />
    </div>

    {{-- Grafik --}}
    <x-ui.card class="mb-6">
        <x-ui.section-header title="Arus Kas 6 Bulan Terakhir" icon="chart" />
        <x-bar-chart :months="$chart" />
    </x-ui.card>

    {{-- Riwayat --}}
    <x-ui.card padding="p-0">
        <div class="flex items-center justify-between gap-3 p-5 border-b border-slate-100">
            <h2 class="font-bold text-slate-900">Riwayat Transaksi</h2>
            <div class="flex items-center gap-1 bg-slate-100 rounded-xl p-1 text-sm">
                @php $tabs = ['' => 'Semua', 'income' => 'Masuk', 'expense' => 'Keluar']; @endphp
                @foreach ($tabs as $val => $label)
                    <a href="{{ route('kas.index', array_filter(['type' => $val])) }}"
                       @class([
                           'px-3 py-1.5 rounded-lg font-medium transition-colors',
                           'bg-white text-brand-700 shadow-sm' => $activeType === ($val ?: null),
                           'text-slate-500 hover:text-slate-700' => $activeType !== ($val ?: null),
                       ])>{{ $label }}</a>
                @endforeach
            </div>
        </div>

        @forelse ($transactions as $trx)
            <div class="flex items-center gap-4 px-5 py-3.5 {{ ! $loop->last ? 'border-b border-slate-50' : '' }}">
                <span @class([
                    'w-10 h-10 rounded-xl flex items-center justify-center shrink-0',
                    'bg-emerald-50 text-emerald-600' => $trx->type === \App\Enums\TransactionType::Income,
                    'bg-red-50 text-red-600' => $trx->type === \App\Enums\TransactionType::Expense,
                ])>
                    <x-ui.icon :name="$trx->type === \App\Enums\TransactionType::Income ? 'trend-up' : 'wallet'" class="w-5 h-5" />
                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-slate-800 truncate">{{ $trx->description }}</p>
                    <p class="text-xs text-slate-400">{{ $trx->category?->name }} · {{ $trx->transaction_date->translatedFormat('d M Y') }}</p>
                </div>
                <span @class([
                    'text-sm font-bold shrink-0',
                    'text-emerald-600' => $trx->type === \App\Enums\TransactionType::Income,
                    'text-red-600' => $trx->type === \App\Enums\TransactionType::Expense,
                ])>
                    {{ $trx->type === \App\Enums\TransactionType::Income ? '+' : '−' }} {{ rupiah($trx->amount) }}
                </span>
            </div>
        @empty
            <x-ui.empty-state icon="wallet" title="Belum ada transaksi" message="Catatan kas RT akan tampil di sini." />
        @endforelse

        @if ($transactions->hasPages())
            <div class="p-4 border-t border-slate-100">{{ $transactions->links() }}</div>
        @endif
    </x-ui.card>
</x-app-layout>
