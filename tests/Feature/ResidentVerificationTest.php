<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ResidentVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShieldSeeder::class);
    }

    private function makeUser(UserStatus $status, string $role): User
    {
        $user = User::create([
            'name' => ucfirst($role),
            'email' => $role . '_' . $status->value . '@example.com',
            'password' => 'password',
            'status' => $status,
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_pending_warga_is_redirected_to_account_status(): void
    {
        $warga = $this->makeUser(UserStatus::Pending, 'warga');

        $this->actingAs($warga)
            ->get('/dashboard')
            ->assertRedirect(route('account.status'));
    }

    public function test_account_status_page_shows_pending_message(): void
    {
        $warga = $this->makeUser(UserStatus::Pending, 'warga');

        $this->actingAs($warga)
            ->get('/account/status')
            ->assertOk()
            ->assertSee('Menunggu Verifikasi');
    }

    public function test_rejected_warga_sees_rejection_reason(): void
    {
        $warga = $this->makeUser(UserStatus::Rejected, 'warga');
        $warga->update(['rejection_reason' => 'Data tidak cocok']);

        $this->actingAs($warga)
            ->get('/account/status')
            ->assertOk()
            ->assertSee('Ditolak')
            ->assertSee('Data tidak cocok');
    }

    public function test_verified_warga_can_access_dashboard(): void
    {
        $warga = $this->makeUser(UserStatus::Active, 'warga');

        $this->actingAs($warga)
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_verified_warga_is_redirected_away_from_status_page(): void
    {
        $warga = $this->makeUser(UserStatus::Active, 'warga');

        $this->actingAs($warga)
            ->get('/account/status')
            ->assertRedirect(route('dashboard'));
    }

    public function test_warga_cannot_access_filament_panel(): void
    {
        $warga = $this->makeUser(UserStatus::Active, 'warga');

        $this->assertFalse($warga->canAccessPanel(filament()->getPanel('admin')));

        $this->actingAs($warga)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_pengurus_and_super_admin_can_access_panel(): void
    {
        $pengurus = $this->makeUser(UserStatus::Active, 'pengurus');
        $superAdmin = $this->makeUser(UserStatus::Active, 'super_admin');

        $panel = filament()->getPanel('admin');

        $this->assertTrue($pengurus->canAccessPanel($panel));
        $this->assertTrue($superAdmin->canAccessPanel($panel));

        $this->actingAs($pengurus)->get('/admin')->assertSuccessful();
    }

    public function test_pengurus_verifies_pending_warga_via_panel(): void
    {
        $pengurus = $this->makeUser(UserStatus::Active, 'pengurus');
        $warga = $this->makeUser(UserStatus::Pending, 'warga');

        Livewire::actingAs($pengurus)
            ->test(ListUsers::class)
            ->callTableAction('verify', $warga);

        $warga->refresh();

        $this->assertSame(UserStatus::Active, $warga->status);
        $this->assertNotNull($warga->verified_at);
        $this->assertSame($pengurus->id, $warga->verified_by);

        // Setelah terverifikasi, warga bisa mengakses dashboard.
        $this->actingAs($warga)->get('/dashboard')->assertOk();
    }

    public function test_pengurus_rejects_pending_warga_with_reason(): void
    {
        $pengurus = $this->makeUser(UserStatus::Active, 'pengurus');
        $warga = $this->makeUser(UserStatus::Pending, 'warga');

        Livewire::actingAs($pengurus)
            ->test(ListUsers::class)
            ->callTableAction('reject', $warga, data: [
                'rejection_reason' => 'Alamat di luar wilayah RT',
            ]);

        $warga->refresh();

        $this->assertSame(UserStatus::Rejected, $warga->status);
        $this->assertSame('Alamat di luar wilayah RT', $warga->rejection_reason);
    }
}
