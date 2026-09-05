<?php

namespace Tests\Feature;

use App\Enums\ComplaintStatus;
use App\Filament\Resources\Complaints\Pages\ListComplaints;
use App\Models\Complaint;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class CommunityPanelTest extends TestCase
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

    public function test_community_resource_pages_render(): void
    {
        $admin = $this->admin();

        foreach ([
            '/admin/announcements',
            '/admin/announcements/create',
            '/admin/complaints',
        ] as $page) {
            $this->actingAs($admin)->get($page)->assertSuccessful();
        }
    }

    public function test_pengurus_updates_complaint_status_via_panel(): void
    {
        $admin = $this->admin();
        $open = Complaint::where('status', ComplaintStatus::Open)->firstOrFail();
        $before = $open->updates()->count();

        Livewire::actingAs($admin)
            ->test(ListComplaints::class)
            ->callTableAction('updateStatus', $open, data: [
                'status' => ComplaintStatus::Resolved->value,
                'note' => 'Sudah ditangani',
                'response' => 'Terima kasih',
            ]);

        $open->refresh();
        $this->assertSame(ComplaintStatus::Resolved, $open->status);
        $this->assertSame($admin->id, $open->handled_by);
        $this->assertNotNull($open->resolved_at);
        $this->assertSame($before + 1, $open->updates()->count());
    }
}
