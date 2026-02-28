<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 🏢 Empresa demo
        $company = Company::firstOrCreate(
            ['slug' => 'demo-company'],
            ['name' => 'Demo Company', 'plan' => 'free']
        );

        $roles = Role::whereIn('name', [
            'super-admin', 'owner', 'admin', 'user'
        ])->get()->keyBy('name');

        // 🌍 SUPER ADMIN GLOBAL
        $super = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'company_id' => null,
            ]
        );
        $super->syncRoles([$roles['super-admin']]);

        // 👑 OWNER
        $owner = User::firstOrCreate(
            ['email' => 'owner@demo.com'],
            [
                'name' => 'Company Owner',
                'password' => Hash::make('123456'),
                'company_id' => $company->id,
            ]
        );
        $owner->syncRoles([$roles['owner']]);

        // 🧑‍💼 ADMIN
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('123456'),
                'company_id' => $company->id,
            ]
        );
        $admin->syncRoles([$roles['admin']]);

        // 👥 USERS
        foreach (['user1', 'user2'] as $i => $u) {
            $user = User::firstOrCreate(
                ['email' => "$u@demo.com"],
                [
                    'name' => 'User ' . ($i + 1),
                    'password' => Hash::make('123456'),
                    'company_id' => $company->id,
                ]
            );

            $user->syncRoles([$roles['user']]);
        }
    }
}
