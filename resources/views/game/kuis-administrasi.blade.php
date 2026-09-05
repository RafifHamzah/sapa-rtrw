<x-app-layout>
    <x-game.quiz
        title="Kuis Administrasi"
        subtitle="Uji pengetahuanmu seputar RT/RW, surat, iuran, & lingkungan."
        emoji="📋"
        :questions="$questions"
        :complete-url="route('game.complete', 'kuis-administrasi')"
        pass-emoji="🎓"
        pass-title="Cerdas!"
        fail-emoji="💪"
        fail-title="Terus Belajar!"
    />
</x-app-layout>
