<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center"><x-ui.icon name="document" class="w-6 h-6" /></span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Surat Pengantar</h1>
                <p class="text-sm text-slate-500">Ajukan surat digital ber-QR, cukup dari rumah.</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Form pengajuan --}}
        <div class="lg:col-span-2">
            <x-ui.card
                x-data="{ typeId: '', types: @js($types->mapWithKeys(fn ($t) => [$t->id => $t->required_fields ?? []])) }">
                <h2 class="font-bold text-slate-900 mb-4">Ajukan Surat Baru</h2>
                <form method="POST" action="{{ route('letters.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Surat</label>
                        <select name="letter_type_id" x-model="typeId" class="field-input" required>
                            <option value="">— Pilih jenis surat —</option>
                            @foreach ($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Field tambahan dinamis sesuai jenis surat --}}
                    <template x-for="field in (types[typeId] || [])" :key="field.name">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1" x-text="field.label + (field.required ? ' *' : '')"></label>
                            <template x-if="field.type === 'textarea'">
                                <textarea :name="'form_data[' + field.name + ']'" rows="2" class="field-input" :required="field.required"></textarea>
                            </template>
                            <template x-if="field.type !== 'textarea'">
                                <input :type="field.type === 'number' ? 'number' : (field.type === 'date' ? 'date' : 'text')"
                                       :name="'form_data[' + field.name + ']'" class="field-input" :required="field.required">
                            </template>
                        </div>
                    </template>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Keperluan</label>
                        <textarea name="purpose" rows="3" class="field-input" placeholder="Contoh: melamar pekerjaan" required></textarea>
                        <x-input-error :messages="$errors->get('purpose')" class="mt-1" />
                    </div>

                    <x-ui.button type="submit" class="w-full">
                        <x-ui.icon name="plus" class="w-5 h-5" /> Kirim Permohonan
                    </x-ui.button>
                </form>
            </x-ui.card>
        </div>

        {{-- Riwayat --}}
        <div class="lg:col-span-3">
            <x-ui.section-header title="Riwayat Permohonan" icon="clock" />
            <div class="space-y-3">
                @forelse ($letters as $letter)
                    <x-ui.card padding="p-4" class="flex items-start gap-4">
                        <span class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                            <x-ui.icon name="document" class="w-5 h-5" />
                        </span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <p class="font-semibold text-slate-800">{{ $letter->letterType->name }}</p>
                                <x-status-badge :status="$letter->status" />
                            </div>
                            <p class="text-sm text-slate-500 mt-0.5">{{ $letter->purpose }}</p>
                            @if ($letter->letter_number)
                                <p class="text-xs text-slate-400 mt-1 font-mono">No. {{ $letter->letter_number }}</p>
                            @endif
                            @if ($letter->isApproved() && $letter->pdf_path)
                                <a href="{{ route('letters.download', $letter) }}"
                                   class="inline-flex items-center gap-1.5 mt-2 text-sm font-semibold text-brand-600 hover:text-brand-700">
                                    <x-ui.icon name="download" class="w-4 h-4" /> Unduh PDF
                                </a>
                            @endif
                        </div>
                    </x-ui.card>
                @empty
                    <x-ui.card><x-ui.empty-state icon="document" title="Belum ada permohonan"
                        message="Ajukan surat pertama Anda lewat formulir di samping." /></x-ui.card>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
