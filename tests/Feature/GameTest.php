<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShieldSeeder::class);
    }

    private function warga(): User
    {
        $user = User::create([
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $user->assignRole('warga');

        return $user;
    }

    public function test_game_pages_render(): void
    {
        $warga = $this->warga();

        $this->actingAs($warga)->get('/game')->assertOk()->assertSee('Pilah Sampah');
        $this->actingAs($warga)->get('/game/pilah-sampah')->assertOk()->assertSee('Pilah Sampah');
        $this->actingAs($warga)->get('/game/kuis-administrasi')->assertOk()->assertSee('Kuis Administrasi');
        $this->actingAs($warga)->get('/game/tebak-surat')->assertOk()->assertSee('Tebak Jenis Surat');
    }

    public function test_tebak_surat_awards_xp(): void
    {
        $this->actingAs($this->warga())
            ->postJson('/game/tebak-surat/complete', ['correct' => 6, 'total' => 7])
            ->assertOk()->assertJson(['awarded' => true, 'xp' => 40]);
    }

    public function test_each_game_awards_xp_separately_per_day(): void
    {
        $warga = $this->warga();

        $this->actingAs($warga)->postJson('/game/pilah-sampah/complete', ['correct' => 8, 'total' => 9])
            ->assertJson(['awarded' => true, 'xp' => 40]);
        // Game berbeda → source_key beda → tetap dapat XP di hari yang sama.
        $this->actingAs($warga)->postJson('/game/kuis-administrasi/complete', ['correct' => 6, 'total' => 7])
            ->assertJson(['awarded' => true, 'xp' => 40]);

        $this->assertSame(80, $warga->fresh()->xp);
    }

    public function test_unknown_game_is_rejected(): void
    {
        $this->actingAs($this->warga())
            ->postJson('/game/game-ngasal/complete', ['correct' => 5, 'total' => 7])
            ->assertStatus(422);
    }

    public function test_completing_game_awards_xp_once_per_day(): void
    {
        $warga = $this->warga();

        $first = $this->actingAs($warga)->postJson('/game/pilah-sampah/complete', ['correct' => 8, 'total' => 9]);
        $first->assertOk()->assertJson(['awarded' => true, 'xp' => 40]);
        $this->assertSame(40, $warga->fresh()->xp);

        // Klaim kedua di hari yang sama tidak menambah XP.
        $second = $this->actingAs($warga)->postJson('/game/pilah-sampah/complete', ['correct' => 9, 'total' => 9]);
        $second->assertOk()->assertJson(['awarded' => false, 'xp' => 0]);
        $this->assertSame(40, $warga->fresh()->xp);
    }

    public function test_low_score_gives_smaller_reward(): void
    {
        $warga = $this->warga();

        $this->actingAs($warga)->postJson('/game/pilah-sampah/complete', ['correct' => 2, 'total' => 9])
            ->assertOk()->assertJson(['awarded' => true, 'xp' => 15]);
    }

    public function test_total_is_validated(): void
    {
        $this->actingAs($this->warga())
            ->postJson('/game/pilah-sampah/complete', ['correct' => 0, 'total' => 0])
            ->assertStatus(422);
    }
}
