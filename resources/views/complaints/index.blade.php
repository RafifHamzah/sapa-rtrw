<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <span class="w-11 h-11 rounded-2xl bg-brand-600 text-white flex items-center justify-center"><x-ui.icon name="alert" class="w-6 h-6" /></span>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Lapor Warga</h1>
                <p class="text-sm text-slate-500">Sampaikan masalah lingkungan, pengurus akan menindaklanjuti.</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
        {{-- Form --}}
        <div class="lg:col-span-2">
            <x-ui.card x-data="{ photoName: '' }">
                <h2 class="font-bold text-slate-900 mb-4">Buat Laporan Baru</h2>
                <form method="POST" action="{{ route('complaints.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Judul</label>
                        <input type="text" name="title" class="field-input" value="{{ old('title') }}" required>
                        <x-input-error :messages="$errors->get('title')" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Kategori</label>
                        <select name="category" class="field-input" required>
                            @foreach ($categories as $category)
                                <option value="{{ $category->value }}">{{ $category->getLabel() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3" class="field-input" required>{{ old('description') }}</textarea>
                        <x-input-error :messages="$errors->get('description')" class="mt-1" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Patokan Lokasi</label>
                        <input type="text" name="location" class="field-input" placeholder="Contoh: depan pos ronda" value="{{ old('location') }}">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Foto <span class="text-slate-400 font-normal">(opsional, maks 4MB)</span></label>
                        <label class="flex items-center gap-3 rounded-xl border-2 border-dashed border-slate-200 px-4 py-3 cursor-pointer hover:border-brand-300 transition-colors">
                            <x-ui.icon name="download" class="w-5 h-5 text-slate-400 rotate-180" />
                            <span class="text-sm text-slate-500" x-text="photoName || 'Pilih foto…'"></span>
                            <input type="file" name="photo" accept="image/*" class="hidden" @change="photoName = $event.target.files[0]?.name ?? ''">
                        </label>
                        <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                    </div>
                    <x-ui.button type="submit" class="w-full">
                        <x-ui.icon name="plus" class="w-5 h-5" /> Kirim Laporan
                    </x-ui.button>
                </form>
            </x-ui.card>
        </div>

        {{-- Daftar --}}
        <div class="lg:col-span-3">
            <x-ui.section-header title="Laporan Saya" icon="clock" />
            <div class="space-y-3">
                @forelse ($complaints as $complaint)
                    <a href="{{ route('complaints.show', $complaint) }}" class="card-surface p-4 flex items-center gap-4 hover:shadow-soft transition-all">
                        @if ($complaint->photo_path)
                            <img src="{{ Storage::url($complaint->photo_path) }}" alt="" class="w-12 h-12 rounded-xl object-cover shrink-0">
                        @else
                            <span class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center shrink-0"><x-ui.icon name="alert" class="w-6 h-6" /></span>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-slate-800 truncate">{{ $complaint->title }}</p>
                            <p class="text-xs text-slate-400">{{ $complaint->category->getLabel() }} · {{ $complaint->created_at->translatedFormat('d M Y') }}</p>
                        </div>
                        <x-status-badge :status="$complaint->status" />
                    </a>
                @empty
                    <x-ui.card><x-ui.empty-state icon="alert" title="Belum ada laporan"
                        message="Laporan yang Anda kirim akan muncul di sini beserta status penanganannya." /></x-ui.card>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
