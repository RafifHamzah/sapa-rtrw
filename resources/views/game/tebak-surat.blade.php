<x-app-layout>
    <x-game.quiz
        title="Tebak Jenis Surat"
        subtitle="Cocokkan kebutuhan warga dengan jenis surat yang tepat."
        emoji="📄"
        :questions="$questions"
        :complete-url="route('game.complete', 'tebak-surat')"
        pass-emoji="📬"
        pass-title="Jago Surat!"
        fail-emoji="💪"
        fail-title="Coba Lagi!"
    />
</x-app-layout>
