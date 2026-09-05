<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\ResidentRelationship;
use App\Filament\Resources\Families\Pages\CreateFamily;
use App\Filament\Resources\Residents\Pages\CreateResident;
use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WargaPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(ShieldSeeder::class);
        $this->seed(DemoSeeder::class);
    }

    private function admin(): User
    {
        return User::where('email', 'admin@rtrw.test')->firstOrFail();
    }

    public function test_warga_and_kk_resource_pages_render_for_admin(): void
    {
        $admin = $this->admin();

        foreach ([
            '/admin/families',
            '/admin/families/create',
            '/admin/residents',
            '/admin/residents/create',
        ] as $page) {
            $this->actingAs($admin)->get($page)->assertSuccessful();
        }
    }

    public function test_pengurus_can_access_warga_data(): void
    {
        $pengurus = User::where('email', 'bendahara@rtrw.test')->firstOrFail();

        $this->actingAs($pengurus)->get('/admin/residents')->assertSuccessful();
        $this->actingAs($pengurus)->get('/admin/families')->assertSuccessful();
    }

    public function test_warga_cannot_access_warga_data_panel(): void
    {
        $warga = User::where('email', 'warga@rtrw.test')->firstOrFail();

        $this->actingAs($warga)->get('/admin/residents')->assertForbidden();
    }

    public function test_admin_can_create_family(): void
    {
        $admin = $this->admin();
        $rt = Rt::firstOrFail();

        Livewire::actingAs($admin)
            ->test(CreateFamily::class)
            ->fillForm([
                'kk_number' => '3201555500001234',
                'rt_id' => $rt->id,
                'address' => 'Jl. Uji Coba No. 1',
                'rt_status' => 'Tetap',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('families', ['kk_number' => '3201555500001234']);
    }

    public function test_admin_can_create_resident_with_encrypted_nik(): void
    {
        $admin = $this->admin();
        $family = Family::firstOrFail();

        Livewire::actingAs($admin)
            ->test(CreateResident::class)
            ->fillForm([
                'family_id' => $family->id,
                'full_name' => 'Warga Uji Coba',
                'nik' => '3201010101010001',
                'gender' => Gender::Male->value,
                'relationship' => ResidentRelationship::Child->value,
                'birth_date' => '2005-06-15',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $resident = Resident::where('full_name', 'Warga Uji Coba')->firstOrFail();
        // NIK tersimpan terenkripsi namun terbaca kembali utuh lewat cast.
        $this->assertSame('3201010101010001', $resident->nik);
        $this->assertSame($family->id, $resident->family_id);
    }

    public function test_kk_number_must_be_unique(): void
    {
        $admin = $this->admin();
        $rt = Rt::firstOrFail();
        $existing = Family::firstOrFail();

        Livewire::actingAs($admin)
            ->test(CreateFamily::class)
            ->fillForm([
                'kk_number' => $existing->kk_number,
                'rt_id' => $rt->id,
                'address' => 'Jl. Duplikat',
            ])
            ->call('create')
            ->assertHasFormErrors(['kk_number']);
    }
}
