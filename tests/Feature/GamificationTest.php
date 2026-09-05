<?php

namespace Tests\Feature;

use App\Enums\Badge;
use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\DuesStatus;
use App\Enums\Gender;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Enums\ResidentRelationship;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Dues;
use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use App\Services\GamificationService;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShieldSeeder::class);
    }

    private function warga(): User
    {
        $rt = Rt::create([
            'number' => '01', 'rw_number' => '01', 'village' => 'X',
            'district' => 'Y', 'city' => 'Z', 'province' => 'P',
        ]);
        $family = Family::create(['rt_id' => $rt->id, 'kk_number' => '3201000000000001', 'address' => 'Jl. A']);
        $user = User::create([
            'rt_id' => $rt->id, 'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $user->assignRole('warga');
        $resident = Resident::create([
            'family_id' => $family->id, 'user_id' => $user->id, 'nik' => '3201010101900001',
            'full_name' => 'Budi', 'gender' => Gender::Male, 'birth_date' => '1990-01-01',
            'relationship' => ResidentRelationship::Head,
        ]);
        $family->update(['head_resident_id' => $resident->id]);

        return $user;
    }

    public function test_level_and_progress_derive_from_xp(): void
    {
        $user = $this->warga();
        $user->update(['xp' => 250]);

        $this->assertSame(3, $user->level());          // 250 / 100 + 1
        $this->assertSame(50, $user->xpIntoLevel());
        $this->assertSame(50, $user->levelProgress());
        $this->assertSame(50, $user->xpToNextLevel());
    }

    public function test_award_is_idempotent_by_source_key(): void
    {
        $user = $this->warga();
        $service = app(GamificationService::class);

        $this->assertTrue($service->award($user, 50, 'Bayar iuran', 'dues_payment:1'));
        $this->assertFalse($service->award($user, 50, 'Bayar iuran', 'dues_payment:1'));

        $this->assertSame(50, $user->fresh()->xp);
        $this->assertSame(1, $user->xpLogs()->count());
    }

    public function test_paying_dues_awards_xp_via_observer(): void
    {
        $user = $this->warga();
        $family = $user->resident->family;
        $dues = $family->dues()->create([
            'rt_id' => $family->rt_id, 'period_month' => 8, 'period_year' => 2026,
            'amount' => 50000, 'status' => DuesStatus::Unpaid, 'due_date' => '2026-08-10',
        ]);

        $dues->payments()->create([
            'amount' => 50000, 'payment_method' => PaymentMethod::Cash,
            'status' => PaymentStatus::Paid, 'paid_at' => '2026-08-05',
        ]);

        // 50 XP (bayar) + 100 (badge Tepat Waktu Bayar bonus).
        $this->assertGreaterThanOrEqual(150, $user->fresh()->xp);
        $this->assertTrue($user->fresh()->hasBadge(Badge::TepatWaktuBayar));
    }

    public function test_badges_unlock_on_criteria(): void
    {
        $user = $this->warga();
        $service = app(GamificationService::class);

        // 3 laporan → Pelapor Aktif; 1 lingkungan → Relawan Lingkungan.
        Complaint::create(['rt_id' => $user->rt_id, 'user_id' => $user->id, 'title' => 'A', 'category' => ComplaintCategory::Environment, 'description' => '.', 'status' => ComplaintStatus::Open]);
        Complaint::create(['rt_id' => $user->rt_id, 'user_id' => $user->id, 'title' => 'B', 'category' => ComplaintCategory::Security, 'description' => '.', 'status' => ComplaintStatus::Open]);
        Complaint::create(['rt_id' => $user->rt_id, 'user_id' => $user->id, 'title' => 'C', 'category' => ComplaintCategory::Other, 'description' => '.', 'status' => ComplaintStatus::Open]);

        $service->syncBadges($user->fresh());

        $this->assertTrue($user->fresh()->hasBadge(Badge::PelaporAktif));
        $this->assertTrue($user->fresh()->hasBadge(Badge::RelawanLingkungan));
    }

    public function test_high_xp_unlocks_tiered_badges(): void
    {
        $user = $this->warga();
        $user->update(['xp' => 1000]);

        app(GamificationService::class)->syncBadges($user->fresh());

        $this->assertTrue($user->fresh()->hasBadge(Badge::WargaTeladan));
        $this->assertTrue($user->fresh()->hasBadge(Badge::KontributorTerbaik));
    }

    public function test_profile_and_leaderboard_pages_render(): void
    {
        $user = $this->warga();
        $user->update(['xp' => 320]);

        $this->actingAs($user)->get('/profil')->assertOk()->assertSee('Level')->assertSee('Koleksi Badge');
        $this->actingAs($user)->get('/peringkat')->assertOk()->assertSee('Papan Peringkat');
    }
}
