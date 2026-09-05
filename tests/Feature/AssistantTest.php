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
use App\Services\ChatbotService;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShieldSeeder::class);
    }

    private function warga(bool $withUnpaidDues = false): User
    {
        $rt = Rt::create([
            'number' => '04', 'rw_number' => '07', 'village' => 'Sukamaju',
            'district' => 'Y', 'city' => 'Z', 'province' => 'P', 'chairman_name' => 'Pak Ketua', 'phone' => '0812',
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

        if ($withUnpaidDues) {
            $family->dues()->create([
                'rt_id' => $rt->id, 'period_month' => 8, 'period_year' => 2026,
                'amount' => 50000, 'status' => DuesStatus::Unpaid,
            ]);
        }

        return $user;
    }

    public function test_answers_how_to_pay_dues_with_action_link(): void
    {
        $response = $this->actingAs($this->warga())->postJson('/assistant/ask', ['message' => 'cara bayar iuran']);

        $response->assertOk()
            ->assertJsonPath('action.url', '/iuran')
            ->assertJsonStructure(['reply', 'suggestions', 'action']);

        $this->assertStringContainsString('Iuran', $response->json('reply'));
    }

    public function test_answers_personal_dues_from_real_data(): void
    {
        $response = $this->actingAs($this->warga(withUnpaidDues: true))
            ->postJson('/assistant/ask', ['message' => 'iuran saya berapa?']);

        $response->assertOk()->assertJsonPath('action.url', '/iuran');
        $this->assertStringContainsString('50.000', $response->json('reply'));
    }

    public function test_answers_cash_balance(): void
    {
        $response = $this->actingAs($this->warga())->postJson('/assistant/ask', ['message' => 'berapa saldo kas rt?']);

        $response->assertOk()->assertJsonPath('action.url', '/kas');
        $this->assertStringContainsString('Rp', $response->json('reply'));
    }

    public function test_unknown_question_returns_fallback_with_suggestions(): void
    {
        $response = $this->actingAs($this->warga())->postJson('/assistant/ask', ['message' => 'asdf qwerty zxcv']);

        $response->assertOk();
        $this->assertNotEmpty($response->json('suggestions'));
        $this->assertStringContainsString('belum paham', $response->json('reply'));
    }

    public function test_message_is_required(): void
    {
        $this->actingAs($this->warga())->postJson('/assistant/ask', ['message' => ''])
            ->assertStatus(422);
    }

    public function test_service_matches_letter_intent(): void
    {
        $result = app(ChatbotService::class)->ask('gimana cara ajukan surat domisili', null);
        $this->assertSame('/letters', $result['action']['url']);
    }
}
