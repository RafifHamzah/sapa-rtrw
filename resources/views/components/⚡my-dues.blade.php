<?php

use App\Enums\DuesStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Dues;
use App\Services\MidtransService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component
{
    /**
     * Tagihan iuran keluarga milik warga yang login.
     */
    #[Computed]
    public function dues(): Collection
    {
        $family = auth()->user()->resident?->family;

        if (! $family) {
            return collect();
        }

        return Dues::where('family_id', $family->id)
            // Eager-load pembayaran online (yang punya order_id) untuk menandai
            // tagihan yang punya transaksi pending → tampilkan tombol "Cek status".
            ->with(['payments' => fn ($q) => $q->whereNotNull('midtrans_order_id')->latest()])
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->get();
    }

    /**
     * Mulai pembayaran online: buat DuesPayment (pending) + Snap token,
     * lalu kirim token ke browser untuk membuka popup Midtrans.
     */
    public function pay(int $duesId): void
    {
        $family = auth()->user()->resident?->family;
        $dues = Dues::where('id', $duesId)->where('family_id', $family?->id)->first();

        if (! $dues || $dues->status === DuesStatus::Paid) {
            $this->dispatch('pay-error', message: 'Tagihan tidak valid atau sudah lunas.');

            return;
        }

        $payment = $dues->payments()
            ->where('status', PaymentStatus::Pending)
            ->latest()
            ->first();

        if (! $payment) {
            $payment = $dues->payments()->create([
                'amount' => $dues->amount,
                'payment_method' => PaymentMethod::Online,
                'status' => PaymentStatus::Pending,
                'recorded_by' => auth()->id(),
            ]);
        }

        // Selalu pakai order_id BARU tiap percobaan bayar. Midtrans menolak
        // pembuatan Snap token untuk order_id yang sudah pernah dipakai
        // ("transaction_details.order_id sudah digunakan").
        $payment->update([
            'midtrans_order_id' => 'DUES-' . $dues->id . '-' . $payment->id . '-' . Str::upper(Str::random(6)),
        ]);

        try {
            $token = app(MidtransService::class)->createSnapToken($payment);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('pay-error', message: 'Pembayaran online belum aktif. Pastikan kunci Midtrans terpasang.');

            return;
        }

        $this->dispatch('open-snap', token: $token, duesId: $dues->id);
    }

    /**
     * Fallback tanpa webhook: cek status transaksi langsung ke Midtrans lalu
     * finalize. Dipanggil otomatis setelah popup Snap sukses (auto-sync) atau
     * manual lewat tombol "Cek status". Idempoten via DuesPaymentObserver.
     */
    #[On('sync-payment')]
    public function syncPayment(int $duesId): void
    {
        $family = auth()->user()->resident?->family;
        $dues = Dues::where('id', $duesId)->where('family_id', $family?->id)->first();

        if (! $dues) {
            return;
        }

        $payment = $dues->payments()
            ->whereNotNull('midtrans_order_id')
            ->latest()
            ->first();

        if (! $payment) {
            $this->dispatch('pay-info', message: 'Belum ada pembayaran online untuk tagihan ini.');

            return;
        }

        try {
            app(MidtransService::class)->syncStatusFromGateway($payment);
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('pay-info', message: 'Belum ada transaksi tercatat di Midtrans. Selesaikan pembayaran dulu, lalu cek lagi.');

            return;
        }

        unset($this->dues); // buang cache computed agar daftar ter-refresh
        $dues->refresh();

        if ($dues->status === DuesStatus::Paid) {
            $this->dispatch('pay-success', message: 'Pembayaran terkonfirmasi — tagihan lunas! 🎉');
        } else {
            $this->dispatch('pay-info', message: 'Pembayaran belum lunas / masih diproses.');
        }
    }
}; ?>

<div>
    @php($items = $this->dues)

    @if ($items->isEmpty())
        <x-ui.card>
            <x-ui.empty-state icon="receipt" title="Belum ada tagihan"
                message="Tagihan iuran untuk keluarga Anda akan muncul di sini setelah pengurus membuatnya." />
        </x-ui.card>
    @else
        <div class="space-y-3">
            @foreach ($items as $d)
                <x-ui.card padding="p-4" class="flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex items-center gap-4 min-w-0 flex-1">
                        <span @class([
                            'w-12 h-12 rounded-2xl flex items-center justify-center shrink-0',
                            'bg-emerald-50 text-emerald-600' => $d->status === DuesStatus::Paid,
                            'bg-amber-50 text-amber-600' => $d->status !== DuesStatus::Paid,
                        ])>
                            <x-ui.icon name="receipt" class="w-6 h-6" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-slate-800">
                                Iuran {{ \Carbon\Carbon::create()->month($d->period_month)->translatedFormat('F') }} {{ $d->period_year }}
                            </p>
                            <p class="text-sm text-slate-500">{{ rupiah($d->amount) }}
                                @if ($d->due_date) · jatuh tempo {{ $d->due_date->translatedFormat('d M Y') }} @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between sm:justify-end gap-3 shrink-0">
                        <x-status-badge :status="$d->status" />
                        @if ($d->status !== DuesStatus::Paid)
                            @if ($d->payments->isNotEmpty())
                                {{-- Fallback: cek status transaksi langsung ke Midtrans (tanpa webhook). --}}
                                <button wire:click="syncPayment({{ $d->id }})" wire:loading.attr="disabled" wire:target="syncPayment({{ $d->id }})"
                                        class="inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-brand-700 ring-1 ring-brand-200 hover:bg-brand-50 disabled:opacity-60">
                                    <span wire:loading.remove wire:target="syncPayment({{ $d->id }})">Cek status</span>
                                    <span wire:loading wire:target="syncPayment({{ $d->id }})">Mengecek…</span>
                                </button>
                            @endif
                            <button wire:click="pay({{ $d->id }})" wire:loading.attr="disabled" wire:target="pay({{ $d->id }})"
                                    class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-700 disabled:opacity-60">
                                <span wire:loading.remove wire:target="pay({{ $d->id }})">Bayar</span>
                                <span wire:loading wire:target="pay({{ $d->id }})">Memproses…</span>
                            </button>
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </div>
    @endif
</div>
