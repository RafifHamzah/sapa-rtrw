<?php

namespace Tests\Feature;

use App\Enums\DuesStatus;
use App\Enums\Gender;
use App\Enums\ResidentRelationship;
use App\Enums\UserStatus;
use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WargaPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShieldSeeder::class);
    }

    private function verifiedWarga(): User
    {
        $rt = Rt::create([
            'number' => '04', 'rw_number' => '07', 'village' => 'Sukamaju',
            'district' => 'Cibinong', 'city' => 'Bogor', 'province' => 'Jabar',
        ]);
        $family = Family::create(['rt_id' => $rt->id, 'kk_number' => '3201000000000009', 'address' => 'Jl. Melati']);
        $user = User::create([
            'rt_id' => $rt->id, 'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $user->assignRole('warga');
        Resident::create([
            'family_id' => $family->id, 'user_id' => $user->id, 'nik' => '3201010101900001',
            'full_name' => 'Budi', 'gender' => Gender::Male, 'birth_date' => '1990-01-01',
            'relationship' => ResidentRelationship::Head,
        ]);
        $family->dues()->create([
            'rt_id' => $rt->id, 'period_month' => 8, 'period_year' => 2026,
            'amount' => 50000, 'status' => DuesStatus::Unpaid,
        ]);

        return $user;
    }

    public function test_public_landing_renders(): void
    {
        $this->get('/')->assertOk()->assertSee('SAPA')->assertSee('Daftar Warga');
    }

    public function test_warga_pages_render_for_verified_user(): void
    {
        $warga = $this->verifiedWarga();

        foreach ([
            '/dashboard',
            '/kas',
            '/iuran',
            '/letters',
            '/announcements',
            '/complaints',
        ] as $page) {
            $this->actingAs($warga)->get($page)->assertOk();
        }
    }

    public function test_iuran_page_lists_dues_via_livewire(): void
    {
        $warga = $this->verifiedWarga();

        $this->actingAs($warga)->get('/iuran')
            ->assertOk()
            ->assertSee('Iuran')
            ->assertSee('Bayar'); // tombol bayar untuk tagihan belum lunas
    }

    public function test_dashboard_renders_for_warga_without_resident(): void
    {
        // Warga terverifikasi tapi belum tertaut data warga — tidak boleh error.
        $user = User::create([
            'name' => 'Baru', 'email' => 'baru@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $user->assignRole('warga');

        $this->actingAs($user)->get('/dashboard')->assertOk();
    }
}
