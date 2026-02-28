<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            UserSeeder::class,
            CashRegisterSeeder::class,
            TransactionSeeder::class,
            NewsSeeder::class,
        ]);
    }
}
