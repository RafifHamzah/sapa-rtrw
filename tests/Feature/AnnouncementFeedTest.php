<?php

namespace Tests\Feature;

use App\Enums\AnnouncementCategory;
use App\Enums\UserStatus;
use App\Models\Announcement;
use App\Models\Rt;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ShieldSeeder::class);
    }

    private function verifiedWarga(Rt $rt): User
    {
        $user = User::create([
            'rt_id' => $rt->id, 'name' => 'Warga', 'email' => 'warga@example.com',
            'password' => 'password', 'status' => UserStatus::Active,
        ]);
        $user->assignRole('warga');

        return $user;
    }

    public function test_feed_shows_published_pinned_first_then_latest(): void
    {
        $rt = Rt::create([
            'number' => '01', 'rw_number' => '01', 'village' => 'X',
            'district' => 'Y', 'city' => 'Z', 'province' => 'P',
        ]);

        Announcement::create(['rt_id' => $rt->id, 'title' => 'Pengumuman Lama', 'category' => AnnouncementCategory::General, 'content' => '...', 'is_pinned' => false, 'published_at' => now()->subDays(5)]);
        Announcement::create(['rt_id' => $rt->id, 'title' => 'Pengumuman Terbaru', 'category' => AnnouncementCategory::General, 'content' => '...', 'is_pinned' => false, 'published_at' => now()->subDay()]);
        Announcement::create(['rt_id' => $rt->id, 'title' => 'Pengumuman Disematkan', 'category' => AnnouncementCategory::Urgent, 'content' => '...', 'is_pinned' => true, 'published_at' => now()->subDays(3)]);
        Announcement::create(['rt_id' => $rt->id, 'title' => 'Draf Tersembunyi', 'category' => AnnouncementCategory::General, 'content' => '...', 'is_pinned' => false, 'published_at' => null]);
        Announcement::create(['rt_id' => $rt->id, 'title' => 'Terjadwal Nanti', 'category' => AnnouncementCategory::General, 'content' => '...', 'is_pinned' => false, 'published_at' => now()->addDays(2)]);

        $response = $this->actingAs($this->verifiedWarga($rt))->get('/announcements');

        $response->assertOk()
            ->assertSeeInOrder(['Pengumuman Disematkan', 'Pengumuman Terbaru', 'Pengumuman Lama'])
            ->assertDontSee('Draf Tersembunyi')
            ->assertDontSee('Terjadwal Nanti');
    }

    public function test_published_scope_excludes_drafts_and_future(): void
    {
        $rt = Rt::create([
            'number' => '01', 'rw_number' => '01', 'village' => 'X',
            'district' => 'Y', 'city' => 'Z', 'province' => 'P',
        ]);
        Announcement::create(['rt_id' => $rt->id, 'title' => 'Tayang', 'category' => AnnouncementCategory::General, 'content' => '...', 'published_at' => now()->subMinute()]);
        Announcement::create(['rt_id' => $rt->id, 'title' => 'Draf', 'category' => AnnouncementCategory::General, 'content' => '...', 'published_at' => null]);
        Announcement::create(['rt_id' => $rt->id, 'title' => 'Nanti', 'category' => AnnouncementCategory::General, 'content' => '...', 'published_at' => now()->addHour()]);

        $this->assertSame(1, Announcement::published()->count());
    }
}
