<?php

namespace Tests\Feature;

use App\Enums\DuesStatus;
use App\Models\Dues;
use App\Models\Family;
use App\Models\Rt;
use App\Services\DuesGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DuesGeneratorTest extends TestCase
{
    use RefreshDatabase;

    private function makeRtWithFamilies(int $count): Rt
    {
        $rt = Rt::create([
            'number' => '001', 'rw_number' => '001', 'village' => 'X',
            'district' => 'Y', 'city' => 'Z', 'province' => 'P',
        ]);

        for ($i = 1; $i <= $count; $i++) {
            Family::create([
                'rt_id' => $rt->id,
                'kk_number' => '32010000000000' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'address' => "Jl. Test No. {$i}",
            ]);
        }

        return $rt;
    }

    public function test_generates_dues_for_all_families(): void
    {
        $rt = $this->makeRtWithFamilies(3);

        $result = app(DuesGenerator::class)->generate($rt->id, 8, 2026, 50000, '2026-08-10');

        $this->assertSame(3, $result['created']);
        $this->assertSame(0, $result['skipped']);
        $this->assertSame(3, Dues::where('period_month', 8)->where('period_year', 2026)->count());

        $dues = Dues::first();
        $this->assertSame(50000, $dues->amount);
        $this->assertSame(DuesStatus::Unpaid, $dues->status);
    }

    public function test_does_not_create_duplicates_on_rerun(): void
    {
        $rt = $this->makeRtWithFamilies(3);

        app(DuesGenerator::class)->generate($rt->id, 8, 2026, 50000, '2026-08-10');
        $second = app(DuesGenerator::class)->generate($rt->id, 8, 2026, 75000, '2026-08-10');

        $this->assertSame(0, $second['created']);
        $this->assertSame(3, $second['skipped']);
        // Tetap 3 tagihan, dan nominal awal tidak berubah.
        $this->assertSame(3, Dues::count());
        $this->assertSame(50000, Dues::first()->amount);
    }
}
