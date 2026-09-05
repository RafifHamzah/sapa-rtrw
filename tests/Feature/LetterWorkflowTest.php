<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\LetterStatus;
use App\Enums\ResidentRelationship;
use App\Enums\UserStatus;
use App\Models\Family;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Resident;
use App\Models\Rt;
use App\Models\User;
use App\Services\LetterService;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LetterWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(ShieldSeeder::class);
    }

    /**
     * @return array{0: Rt, 1: Resident, 2: User, 3: LetterType}
     */
    private function scaffold(): array
    {
        $rt = Rt::create([
            'number' => '05', 'rw_number' => '02', 'village' => 'Sukamaju',
            'district' => 'Cibinong', 'city' => 'Bogor', 'province' => 'Jabar',
        ]);
        $family = Family::create([
            'rt_id' => $rt->id, 'kk_number' => '3201000000000001', 'address' => 'Jl. Mawar 1',
        ]);
        $warga = User::create([
            'rt_id' => $rt->id, 'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $warga->assignRole('warga');
        $resident = Resident::create([
            'family_id' => $family->id, 'user_id' => $warga->id,
            'nik' => '3201010101900001', 'full_name' => 'Budi Santoso',
            'gender' => Gender::Male, 'birth_date' => '1990-01-01',
            'relationship' => ResidentRelationship::Head, 'occupation' => 'Wiraswasta',
        ]);
        $type = LetterType::create([
            'rt_id' => $rt->id, 'name' => 'Surat Keterangan Domisili', 'code' => 'DOM',
            'template_body' => "Nama: {nama}\nNIK: {nik}\nAlamat: {alamat}\nKeperluan: {keperluan}",
            'is_active' => true,
        ]);

        return [$rt, $resident, $warga, $type];
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

    public function test_warga_can_submit_letter_request(): void
    {
        [$rt, $resident, $warga, $type] = $this->scaffold();

        $this->actingAs($warga)->post('/letters', [
            'letter_type_id' => $type->id,
            'purpose' => 'Melamar pekerjaan',
        ])->assertRedirect();

        $letter = LetterRequest::first();
        $this->assertNotNull($letter);
        $this->assertSame(LetterStatus::Pending, $letter->status);
        $this->assertSame($resident->id, $letter->resident_id);
    }

    public function test_required_fields_are_enforced(): void
    {
        [$rt, $resident, $warga] = $this->scaffold();
        $type = LetterType::create([
            'rt_id' => $rt->id, 'name' => 'Pengantar Usaha', 'code' => 'USAHA',
            'template_body' => 'Usaha: {nama_usaha}',
            'required_fields' => [
                ['name' => 'nama_usaha', 'label' => 'Nama Usaha', 'type' => 'text', 'required' => true],
            ],
            'is_active' => true,
        ]);

        $this->actingAs($warga)
            ->post('/letters', ['letter_type_id' => $type->id, 'purpose' => 'Izin usaha'])
            ->assertSessionHasErrors('form_data.nama_usaha');

        $this->actingAs($warga)->post('/letters', [
            'letter_type_id' => $type->id,
            'purpose' => 'Izin usaha',
            'form_data' => ['nama_usaha' => 'Warung Budi'],
        ])->assertRedirect();

        $this->assertSame('Warung Budi', LetterRequest::first()->form_data['nama_usaha']);
    }

    public function test_approve_generates_number_token_and_pdf(): void
    {
        [$rt, $resident, $warga, $type] = $this->scaffold();
        $pengurus = $this->pengurus($rt);

        $letter = LetterRequest::create([
            'rt_id' => $rt->id, 'letter_type_id' => $type->id, 'resident_id' => $resident->id,
            'requested_by' => $warga->id, 'purpose' => 'Melamar pekerjaan', 'status' => LetterStatus::Pending,
        ]);

        app(LetterService::class)->approve($letter, $pengurus);
        $letter->refresh();

        $this->assertSame(LetterStatus::Approved, $letter->status);
        $this->assertSame('001/DOM/RT05/' . now()->year, $letter->letter_number);
        $this->assertSame(40, strlen($letter->qr_token));
        $this->assertNotNull($letter->pdf_path);
        Storage::disk('public')->assertExists($letter->pdf_path);
    }

    public function test_letter_number_increments_per_rt_per_year(): void
    {
        [$rt, $resident, $warga, $type] = $this->scaffold();
        $pengurus = $this->pengurus($rt);

        foreach (['a', 'b', 'c'] as $i) {
            $letter = LetterRequest::create([
                'rt_id' => $rt->id, 'letter_type_id' => $type->id, 'resident_id' => $resident->id,
                'purpose' => "Keperluan {$i}", 'status' => LetterStatus::Pending,
            ]);
            app(LetterService::class)->approve($letter, $pengurus);
        }

        $numbers = LetterRequest::whereNotNull('letter_number')->orderBy('id')->pluck('letter_number')->all();
        $year = now()->year;
        $this->assertSame([
            "001/DOM/RT05/{$year}",
            "002/DOM/RT05/{$year}",
            "003/DOM/RT05/{$year}",
        ], $numbers);
    }

    public function test_public_verify_shows_valid_letter(): void
    {
        [$rt, $resident, $warga, $type] = $this->scaffold();
        $pengurus = $this->pengurus($rt);
        $letter = LetterRequest::create([
            'rt_id' => $rt->id, 'letter_type_id' => $type->id, 'resident_id' => $resident->id,
            'purpose' => 'Keperluan', 'status' => LetterStatus::Pending,
        ]);
        app(LetterService::class)->approve($letter, $pengurus);

        $this->get('/verify/' . $letter->fresh()->qr_token)
            ->assertOk()
            ->assertSee('Surat Terverifikasi')
            ->assertSee($letter->fresh()->letter_number)
            ->assertSee('Budi Santoso');
    }

    public function test_public_verify_rejects_unknown_token(): void
    {
        $this->get('/verify/' . str_repeat('f', 64))
            ->assertOk()
            ->assertSee('Tidak Ditemukan');
    }

    public function test_pending_letter_is_not_publicly_verifiable(): void
    {
        [$rt, $resident, $warga, $type] = $this->scaffold();
        $letter = LetterRequest::create([
            'rt_id' => $rt->id, 'letter_type_id' => $type->id, 'resident_id' => $resident->id,
            'purpose' => 'Keperluan', 'status' => LetterStatus::Pending, 'qr_token' => str_repeat('a', 64),
        ]);

        $this->get('/verify/' . str_repeat('a', 64))
            ->assertOk()
            ->assertSee('Tidak Ditemukan');
    }

    public function test_warga_can_download_own_approved_letter(): void
    {
        [$rt, $resident, $warga, $type] = $this->scaffold();
        $pengurus = $this->pengurus($rt);
        $letter = LetterRequest::create([
            'rt_id' => $rt->id, 'letter_type_id' => $type->id, 'resident_id' => $resident->id,
            'requested_by' => $warga->id, 'purpose' => 'Keperluan', 'status' => LetterStatus::Pending,
        ]);
        app(LetterService::class)->approve($letter, $pengurus);

        $this->actingAs($warga)->get("/letters/{$letter->id}/download")->assertOk();
        // Setelah diunduh, status jadi completed.
        $this->assertSame(LetterStatus::Completed, $letter->fresh()->status);
    }

    public function test_warga_cannot_download_others_letter(): void
    {
        [$rt, $resident, $warga, $type] = $this->scaffold();
        $pengurus = $this->pengurus($rt);
        $letter = LetterRequest::create([
            'rt_id' => $rt->id, 'letter_type_id' => $type->id, 'resident_id' => $resident->id,
            'purpose' => 'Keperluan', 'status' => LetterStatus::Pending,
        ]);
        app(LetterService::class)->approve($letter, $pengurus);

        $other = User::create([
            'rt_id' => $rt->id, 'name' => 'Lain', 'email' => 'lain@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $other->assignRole('warga');

        $this->actingAs($other)->get("/letters/{$letter->id}/download")->assertForbidden();
    }
}
