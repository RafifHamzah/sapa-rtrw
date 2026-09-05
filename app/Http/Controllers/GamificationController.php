<?php

namespace App\Http\Controllers;

use App\Enums\Badge;
use App\Services\GamificationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class GamificationController extends Controller
{
    public function __construct(private readonly GamificationService $gamification) {}

    /**
     * Halaman profil & prestasi warga (level, XP, badge, riwayat kontribusi).
     */
    public function profile(Request $request): View
    {
        $user = $request->user()->load(['resident.family.rt', 'badges']);
        $resident = $user->resident;
        $family = $resident?->family;

        $stats = [
            'badges' => $user->badges->count(),
            'complaints' => \App\Models\Complaint::where('user_id', $user->id)->count(),
            'letters' => $resident ? \App\Models\LetterRequest::where('resident_id', $resident->id)->count() : 0,
            'dues_paid' => $family
                ? \App\Models\Dues::where('family_id', $family->id)->where('status', \App\Enums\DuesStatus::Paid)->count()
                : 0,
        ];

        return view('profile.show', [
            'user' => $user,
            'resident' => $resident,
            'stats' => $stats,
            'allBadges' => Badge::cases(),
            'xpLogs' => $user->xpLogs()->take(12)->get(),
            'rank' => $this->gamification->leaderboard(1000)
                ->search(fn ($u) => $u->id === $user->id),
        ]);
    }

    /**
     * Papan peringkat warga berdasarkan XP.
     */
    public function leaderboard(Request $request): View
    {
        return view('leaderboard', [
            'leaders' => $this->gamification->leaderboard(20),
            'currentUserId' => $request->user()->id,
        ]);
    }
}
