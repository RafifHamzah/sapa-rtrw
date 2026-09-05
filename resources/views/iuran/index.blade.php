<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center"><x-ui.icon name="receipt" class="w-6 h-6" /></span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Iuran Saya</h1>
                <p class="text-sm text-slate-500">Bayar iuran bulanan dengan aman lewat Midtrans.</p>
            </div>
        </div>
    </x-slot>

    <livewire:my-dues />

    @push('scripts')
        <script src="https://app.sandbox.midtrans.com/snap/snap.js"
                data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('open-snap', (event) => {
                    const data = Array.isArray(event) ? event[0] : event;
                    const token = data?.token ?? null;
                    const duesId = data?.duesId ?? null;
                    if (!token || typeof window.snap === 'undefined') {
                        window.toast && window.toast('Pembayaran online belum tersedia saat ini.', 'error');
                        return;
                    }
                    window.snap.pay(token, {
                        // Auto-sync: tarik status dari Midtrans setelah bayar (webhook tak sampai localhost).
                        onSuccess: () => {
                            window.confettiBurst && window.confettiBurst();
                            window.toast && window.toast('Pembayaran berhasil! Menyinkronkan status…', 'success');
                            Livewire.dispatch('sync-payment', { duesId });
                        },
                        onPending: () => {
                            window.toast && window.toast('Pembayaran diproses. Menyinkronkan status…', 'info');
                            Livewire.dispatch('sync-payment', { duesId });
                        },
                        onError: () => window.toast && window.toast('Pembayaran gagal. Silakan coba lagi.', 'error'),
                        onClose: () => {},
                    });
                });

                const toastFrom = (event, type) => {
                    const data = Array.isArray(event) ? event[0] : event;
                    const msg = data?.message ?? 'Terjadi kesalahan.';
                    window.toast ? window.toast(msg, type) : alert(msg);
                };
                Livewire.on('pay-error', (event) => toastFrom(event, 'error'));
                Livewire.on('pay-info', (event) => toastFrom(event, 'info'));
                Livewire.on('pay-success', (event) => {
                    window.confettiBurst && window.confettiBurst();
                    toastFrom(event, 'success');
                });
            });
        </script>
    @endpush
</x-app-layout>
