<?php

namespace Tests\Feature;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\Gender;
use App\Enums\ResidentRelationship;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Family;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use App\Services\ComplaintService;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ComplaintWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(ShieldSeeder::class);
    }

    /**
     * @return array{0: Rt, 1: User}
     */
    private function wargaWithResident(): array
    {
        $rt = Rt::create([
            'number' => '01', 'rw_number' => '01', 'village' => 'X',
            'district' => 'Y', 'city' => 'Z', 'province' => 'P',
        ]);
        $family = Family::create(['rt_id' => $rt->id, 'kk_number' => '3201000000000001', 'address' => 'Jl. A']);
        $warga = User::create([
            'rt_id' => $rt->id, 'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $warga->assignRole('warga');
        Resident::create([
            'family_id' => $family->id, 'user_id' => $warga->id, 'nik' => '3201010101900001',
            'full_name' => 'Budi', 'gender' => Gender::Male, 'birth_date' => '1990-01-01',
            'relationship' => ResidentRelationship::Head,
        ]);

        return [$rt, $warga];
    }

    private function pengurus(Rt $rt): User
    {
        $user = User::create([
            'rt_id' => $rt->id, 'name' => 'Pengurus', 'email' => 'pengurus@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $user->assignRole('pengurus');

        return $user;
    }

    public function test_warga_submits_complaint_with_photo(): void
    {
        [$rt, $warga] = $this->wargaWithResident();

        $this->actingAs($warga)->post('/complaints', [
            'title' => 'Jalan berlubang',
            'description' => 'Lubang besar di depan gang.',
            'category' => ComplaintCategory::Infrastructure->value,
            'location' => 'Gang Mawar',
            'photo' => UploadedFile::fake()->image('lubang.jpg'),
        ])->assertRedirect();

        $complaint = Complaint::first();
        $this->assertNotNull($complaint);
        $this->assertSame(ComplaintStatus::Open, $complaint->status);
        $this->assertSame($warga->id, $complaint->user_id);
        $this->assertNotNull($complaint->photo_path);
        Storage::disk('public')->assertExists($complaint->photo_path);
    }

    public function test_photo_must_be_valid_image(): void
    {
        [$rt, $warga] = $this->wargaWithResident();

        $this->actingAs($warga)->post('/complaints', [
            'title' => 'Test',
            'description' => 'desc',
            'category' => ComplaintCategory::Other->value,
            'photo' => UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('photo');
    }

    public function test_new_complaint_creates_initial_timeline_and_notifies_managers(): void
    {
        [$rt, $warga] = $this->wargaWithResident();
        $pengurus = $this->pengurus($rt);

        $this->actingAs($warga)->post('/complaints', [
            'title' => 'Lampu mati',
            'description' => 'Gelap',
            'category' => ComplaintCategory::Infrastructure->value,
        ]);

        $complaint = Complaint::first();
        $this->assertSame(1, $complaint->updates()->count());
        $this->assertSame(ComplaintStatus::Open, $complaint->updates()->first()->status);

        // Notifikasi in-app terkirim ke pengurus.
        $this->assertSame(1, $pengurus->notifications()->count());
    }

    public function test_status_workflow_records_timeline_and_metadata(): void
    {
        [$rt, $warga] = $this->wargaWithResident();
        $pengurus = $this->pengurus($rt);
        $complaint = Complaint::create([
            'rt_id' => $rt->id, 'user_id' => $warga->id, 'title' => 'X',
            'category' => ComplaintCategory::Security, 'description' => '...', 'status' => ComplaintStatus::Open,
        ]);

        $service = app(ComplaintService::class);
        $service->changeStatus($complaint, ComplaintStatus::InProgress, $pengurus, 'Dicek petugas');
        $service->changeStatus($complaint->fresh(), ComplaintStatus::Resolved, $pengurus, 'Selesai diperbaiki');

        $complaint->refresh();
        $this->assertSame(ComplaintStatus::Resolved, $complaint->status);
        $this->assertSame($pengurus->id, $complaint->handled_by);
        $this->assertNotNull($complaint->resolved_at);
        // 1 entri awal (observer) + 2 perubahan status = 3.
        $this->assertSame(3, $complaint->updates()->count());
    }

    public function test_warga_can_view_own_complaint_but_not_others(): void
    {
        [$rt, $warga] = $this->wargaWithResident();
        $complaint = Complaint::create([
            'rt_id' => $rt->id, 'user_id' => $warga->id, 'title' => 'Punyaku',
            'category' => ComplaintCategory::Other, 'description' => '...', 'status' => ComplaintStatus::Open,
        ]);

        $this->actingAs($warga)->get("/complaints/{$complaint->id}")->assertOk()->assertSee('Punyaku');

        $other = User::create([
            'rt_id' => $rt->id, 'name' => 'Lain', 'email' => 'lain@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $other->assignRole('warga');
        $this->actingAs($other)->get("/complaints/{$complaint->id}")->assertForbidden();
    }
}
