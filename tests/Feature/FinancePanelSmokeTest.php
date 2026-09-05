<?php

namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ShieldSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancePanelSmokeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(ShieldSeeder::class);
        $this->seed(DemoSeeder::class);

        return User::where('email', 'admin@rtrw.test')->firstOrFail();
    }

    public function test_finance_resource_pages_render(): void
    {
        $admin = $this->admin();

        $pages = [
            '/admin', // dashboard + widgets
            '/admin/transaction-categories',
            '/admin/transaction-categories/create',
            '/admin/transactions',
            '/admin/transactions/create',
            '/admin/dues',
            '/admin/dues/create',
            '/admin/dues-payments',
            '/admin/dues-payments/create',
        ];

        foreach ($pages as $page) {
            $this->actingAs($admin)->get($page)->assertSuccessful();
        }
    }
}
