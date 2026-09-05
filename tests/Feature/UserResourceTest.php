<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        $this->seed(ShieldSeeder::class);

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'status' => UserStatus::Active,
        ]);
        $user->assignRole('super_admin');

        return $user;
    }

    public function test_admin_can_render_user_resource_pages(): void
    {
        $admin = $this->superAdmin();
        $target = User::create([
            'name' => 'Target',
            'email' => 'target@example.com',
            'password' => 'password',
            'status' => UserStatus::Pending,
        ]);
        $target->assignRole('warga');

        $this->actingAs($admin)->get('/admin/users')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/users/create')->assertSuccessful();
        $this->actingAs($admin)->get("/admin/users/{$target->id}/edit")->assertSuccessful();
    }
}
