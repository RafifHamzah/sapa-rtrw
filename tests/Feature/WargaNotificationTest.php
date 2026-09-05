<?php

namespace Tests\Feature;

use App\Enums\ComplaintCategory;
use App\Enums\ComplaintStatus;
use App\Enums\Gender;
use App\Enums\LetterStatus;
use App\Enums\ResidentRelationship;
use App\Enums\UserStatus;
use App\Models\Complaint;
use App\Models\Family;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use App\Services\ComplaintService;
use App\Services\LetterService;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WargaNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(ShieldSeeder::class);
    }

    /**
     * @return array{0: Rt, 1: Resident, 2: User, 3: User}
     */
    private function scaffold(): array
    {
        $rt = Rt::create([
            'number' => '05', 'rw_number' => '02', 'village' => 'Sukamaju',
            'district' => 'Cibinong', 'city' => 'Bogor', 'province' => 'Jabar',
        ]);
        $family = Family::create(['rt_id' => $rt->id, 'kk_number' => '3201000000000001', 'address' => 'Jl. Mawar 1']);
        $warga = User::create([
            'rt_id' => $rt->id, 'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $warga->assignRole('warga');
        $resident = Resident::create([
            'family_id' => $family->id, 'user_id' => $warga->id, 'nik' => '3201010101900001',
            'full_name' => 'Budi Santoso', 'gender' => Gender::Male, 'birth_date' => '1990-01-01',
            'relationship' => ResidentRelationship::Head,
        ]);
        $pengurus = User::create([
            'rt_id' => $rt->id, 'name' => 'Pengurus', 'email' => 'pengurus@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $pengurus->assignRole('pengurus');

        return [$rt, $resident, $warga, $pengurus];
    }

    public function test_letter_approval_notifies_requester(): void
    {
        [$rt, $resident, $warga, $pengurus] = $this->scaffold();
        $type = LetterType::create([
            'rt_id' => $rt->id, 'name' => 'Surat Domisili', 'code' => 'DOM',
            'template_body' => 'Nama: {nama}', 'is_active' => true,
        ]);
        $letter = LetterRequest::create([
            'rt_id' => $rt->id, 'letter_type_id' => $type->id, 'resident_id' => $resident->id,
            'requested_by' => $warga->id, 'purpose' => 'Melamar kerja', 'status' => LetterStatus::Pending,
        ]);

        app(LetterService::class)->approve($letter, $pengurus);

        $this->assertCount(1, $warga->fresh()->unreadNotifications);
        $data = $warga->fresh()->notifications->first()->data;
        $this->assertStringContainsString('disetujui', $data['title']);
        $this->assertSame(route('letters.index'), $data['url']);
    }

    public function test_letter_rejection_notifies_requester(): void
    {
        [$rt, $resident, $warga, $pengurus] = $this->scaffold();
        $type = LetterType::create([
            'rt_id' => $rt->id, 'name' => 'Surat Domisili', 'code' => 'DOM',
            'template_body' => 'Nama: {nama}', 'is_active' => true,
        ]);
        $letter = LetterRequest::create([
            'rt_id' => $rt->id, 'letter_type_id' => $type->id, 'resident_id' => $resident->id,
            'requested_by' => $warga->id, 'purpose' => 'Melamar kerja', 'status' => LetterStatus::Pending,
        ]);

        app(LetterService::class)->reject($letter, $pengurus, 'Data belum lengkap');

        $data = $warga->fresh()->notifications->first()->data;
        $this->assertStringContainsString('ditolak', $data['title']);
        $this->assertStringContainsString('Data belum lengkap', $data['body']);
    }

    public function test_complaint_resolution_notifies_reporter(): void
    {
        [$rt, $resident, $warga, $pengurus] = $this->scaffold();
        $complaint = Complaint::create([
            'rt_id' => $rt->id, 'user_id' => $warga->id, 'category' => ComplaintCategory::Infrastructure,
            'title' => 'Jalan rusak', 'description' => 'Berlubang besar', 'status' => ComplaintStatus::Open,
        ]);

        app(ComplaintService::class)->changeStatus($complaint, ComplaintStatus::Resolved, $pengurus);

        $data = $warga->fresh()->notifications->first()->data;
        $this->assertStringContainsString('selesai', $data['title']);
        $this->assertSame(route('complaints.show', $complaint), $data['url']);
    }

    public function test_complaint_status_change_via_edit_page_still_notifies(): void
    {
        // Perubahan status tanpa lewat service (mis. halaman edit Filament) tetap
        // memicu notifikasi karena logika ada di observer.
        [$rt, $resident, $warga, $pengurus] = $this->scaffold();
        $complaint = Complaint::create([
            'rt_id' => $rt->id, 'user_id' => $warga->id, 'category' => ComplaintCategory::Infrastructure,
            'title' => 'Lampu mati', 'description' => 'Gelap', 'status' => ComplaintStatus::Open,
        ]);

        $complaint->update(['status' => ComplaintStatus::InProgress]);

        $this->assertCount(1, $warga->fresh()->unreadNotifications);
    }

    public function test_non_status_update_does_not_notify(): void
    {
        [$rt, $resident, $warga, $pengurus] = $this->scaffold();
        $complaint = Complaint::create([
            'rt_id' => $rt->id, 'user_id' => $warga->id, 'category' => ComplaintCategory::Infrastructure,
            'title' => 'Sampah', 'description' => 'Menumpuk', 'status' => ComplaintStatus::Open,
        ]);

        $complaint->update(['title' => 'Sampah menumpuk']);

        $this->assertCount(0, $warga->fresh()->unreadNotifications);
    }

    public function test_warga_can_mark_notification_read_and_is_redirected(): void
    {
        [$rt, $resident, $warga, $pengurus] = $this->scaffold();
        $complaint = Complaint::create([
            'rt_id' => $rt->id, 'user_id' => $warga->id, 'category' => ComplaintCategory::Infrastructure,
            'title' => 'Jalan rusak', 'description' => 'Berlubang', 'status' => ComplaintStatus::Open,
        ]);
        app(ComplaintService::class)->changeStatus($complaint, ComplaintStatus::Resolved, $pengurus);

        $notification = $warga->fresh()->notifications->first();

        $this->actingAs($warga)
            ->get(route('notifications.read', $notification->id))
            ->assertRedirect(route('complaints.show', $complaint));

        $this->assertCount(0, $warga->fresh()->unreadNotifications);
    }

    public function test_mark_all_read(): void
    {
        [$rt, $resident, $warga, $pengurus] = $this->scaffold();
        foreach (['A', 'B'] as $t) {
            $c = Complaint::create([
                'rt_id' => $rt->id, 'user_id' => $warga->id, 'category' => ComplaintCategory::Infrastructure,
                'title' => $t, 'description' => 'x', 'status' => ComplaintStatus::Open,
            ]);
            app(ComplaintService::class)->changeStatus($c, ComplaintStatus::Resolved, $pengurus);
        }

        $this->assertCount(2, $warga->fresh()->unreadNotifications);

        $this->actingAs($warga)->post(route('notifications.read-all'))->assertRedirect();

        $this->assertCount(0, $warga->fresh()->unreadNotifications);
    }
}
