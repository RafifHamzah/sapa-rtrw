<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // ShieldSeeder membuat role (super_admin, pengurus, warga) beserta
            // permission-nya, harus jalan sebelum DemoSeeder menautkan role ke user.
            ShieldSeeder::class,
            DemoSeeder::class,
        ]);
    }
}
