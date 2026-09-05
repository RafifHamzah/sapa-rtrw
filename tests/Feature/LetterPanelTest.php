<?php

namespace Tests\Feature;

use App\Enums\LetterStatus;
use App\Filament\Resources\LetterRequests\Pages\ListLetterRequests;
use App\Models\LetterRequest;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class LetterPanelTest extends TestCase
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

    public function test_letter_resource_pages_render(): void
    {
        $admin = $this->admin();

        foreach ([
            '/admin/letter-types',
            '/admin/letter-types/create',
            '/admin/letter-requests',
        ] as $page) {
            $this->actingAs($admin)->get($page)->assertSuccessful();
        }
    }

    public function test_pengurus_approves_letter_via_panel(): void
    {
        $admin = $this->admin();
        $pending = LetterRequest::where('status', LetterStatus::Pending)->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ListLetterRequests::class)
            ->callTableAction('approve', $pending);

        $pending->refresh();
        $this->assertSame(LetterStatus::Approved, $pending->status);
        $this->assertNotNull($pending->letter_number);
        $this->assertNotNull($pending->qr_token);
        Storage::disk('public')->assertExists($pending->pdf_path);
    }
}
