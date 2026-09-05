<?php

namespace App\Services;

use App\Enums\Badge;
use App\Enums\ComplaintCategory;
use App\Enums\DuesStatus;
use App\Enums\LetterStatus;
use App\Models\Complaint;
use App\Models\Dues;
use App\Models\DuesPayment;
use App\Models\LetterRequest;
use App\Models\User;
use App\Models\UserBadge;
use App\Models\XpLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Beri XP ke user. Idempoten: satu source_key hanya memberi XP sekali.
     * Mengembalikan true bila XP baru diberikan.
     */
    public function award(User $user, int $points, string $reason, ?string $sourceKey = null): bool
    {
        return DB::transaction(function () use ($user, $points, $reason, $sourceKey): bool {
            if ($sourceKey !== null
                && XpLog::where('user_id', $user->id)->where('source_key', $sourceKey)->exists()) {
                return false;
            }

            XpLog::create([
                'user_id' => $user->id,
                'points' => $points,
                'reason' => $reason,
                'source_key' => $sourceKey,
            ]);

            $user->increment('xp', $points);

            return true;
        });
    }

    /**
     * Catat aktivitas warga: beri XP lalu evaluasi badge.
     *
     * @return array<int, Badge> badge yang baru diraih
     */
    public function recordActivity(User $user, int $points, string $reason, ?string $sourceKey = null): array
    {
        $this->award($user, $points, $reason, $sourceKey);

        return $this->syncBadges($user);
    }

    /**
     * Berikan semua badge yang memenuhi syarat namun belum dimiliki.
     * Diulang beberapa putaran karena bonus XP badge bisa membuka badge lain.
     *
     * @return array<int, Badge>
     */
    public function syncBadges(User $user): array
    {
        $awarded = [];

        for ($i = 0; $i < 5; $i++) {
            $round = $this->awardQualifiedOnce($user);
            if ($round === []) {
                break;
            }
            $awarded = array_merge($awarded, $round);
        }

        return $awarded;
    }

    /**
     * @return array<int, Badge>
     */
    private function awardQualifiedOnce(User $user): array
    {
        $user->refresh();
        $stats = $this->stats($user);
        $new = [];

        foreach (Badge::cases() as $badge) {
            if (! $this->qualifies($badge, $user, $stats)) {
                continue;
            }

            // firstOrCreate = idempoten (aman dari pluck/cast & pemanggilan ganda).
            $record = UserBadge::firstOrCreate(
                ['user_id' => $user->id, 'badge' => $badge->value],
                ['awarded_at' => now()],
            );

            if ($record->wasRecentlyCreated) {
                $this->award($user, $badge->xpBonus(), 'Badge: ' . $badge->getLabel(), 'badge:' . $badge->value);
                $new[] = $badge;
            }
        }

        return $new;
    }

    /**
     * @param  array<string, int|bool>  $stats
     */
    private function qualifies(Badge $badge, User $user, array $stats): bool
    {
        return match ($badge) {
            Badge::RajinBayarIuran => $stats['dues_paid'] >= 3,
            Badge::TepatWaktuBayar => (bool) $stats['on_time'],
            Badge::PelaporAktif => $stats['complaints'] >= 3,
            Badge::RelawanLingkungan => $stats['environment_reports'] >= 1,
            Badge::UmkmInspiratif => (bool) $stats['usaha_approved'],
            Badge::AktifGotongRoyong => $stats['total_activities'] >= 5,
            Badge::WargaTeladan => $user->xp >= 500,
            Badge::KontributorTerbaik => $user->xp >= 1000,
        };
    }

    /**
     * @return array<string, int|bool>
     */
    private function stats(User $user): array
    {
        $family = $user->resident?->family;
        $residentId = $user->resident?->id;

        $duesPaid = $family
            ? Dues::where('family_id', $family->id)->where('status', DuesStatus::Paid)->count()
            : 0;

        $onTime = false;
        if ($family) {
            $onTime = DuesPayment::with('dues')
                ->where('status', \App\Enums\PaymentStatus::Paid)
                ->whereHas('dues', fn ($q) => $q->where('family_id', $family->id))
                ->get()
                ->contains(fn (DuesPayment $p) => $p->paid_at && $p->dues?->due_date
                    && $p->paid_at->lte($p->dues->due_date->endOfDay()));
        }

        $complaints = Complaint::where('user_id', $user->id)->count();
        $environmentReports = Complaint::where('user_id', $user->id)
            ->where('category', ComplaintCategory::Environment)->count();

        $letters = $residentId ? LetterRequest::where('resident_id', $residentId)->count() : 0;
        $usahaApproved = $residentId && LetterRequest::where('resident_id', $residentId)
            ->whereIn('status', [LetterStatus::Approved, LetterStatus::Completed])
            ->whereHas('letterType', fn ($q) => $q->where('code', 'USAHA'))
            ->exists();

        return [
            'dues_paid' => $duesPaid,
            'on_time' => $onTime,
            'complaints' => $complaints,
            'environment_reports' => $environmentReports,
            'usaha_approved' => $usahaApproved,
            'total_activities' => $duesPaid + $complaints + $letters,
        ];
    }

    /**
     * Papan peringkat warga berdasarkan XP.
     */
    public function leaderboard(int $limit = 10): Collection
    {
        return User::role('warga')
            ->where('status', \App\Enums\UserStatus::Active)
            ->withCount('badges')
            ->orderByDesc('xp')
            ->orderBy('name')
            ->take($limit)
            ->get();
    }
}
