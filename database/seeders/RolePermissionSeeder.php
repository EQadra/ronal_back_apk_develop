<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'api';

        // 🏢 Empresa demo
        $company = Company::firstOrCreate(
            ['slug' => 'demo-company'],
            ['name' => 'Demo Company', 'plan' => 'free']
        );

        // 🔐 Permisos
        $permissions = [
            'ver dashboard',
            'gestionar usuarios',
            'create news',
            'edit news',
            'delete news',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => $guard,
            ]);
        }

        // 🔐 Roles
        $superRole = Role::firstOrCreate([
            'name' => 'super-admin',
            'guard_name' => $guard,
        ]);

        $ownerRole = Role::firstOrCreate([
            'name' => 'owner',
            'guard_name' => $guard,
        ]);

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard,
        ]);

        $userRole = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => $guard,
        ]);

        // 🔓 Asignar permisos
        $superRole->syncPermissions(Permission::all());
        $adminRole->syncPermissions([
            'ver dashboard',
            'gestionar usuarios',
            'create news',
            'edit news',
        ]);
        $userRole->syncPermissions(['ver dashboard']);

        // 🌍 SUPER ADMIN
        $super = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('12345678'),
                'company_id' => null,
            ]
        );
        $super->assignRole($superRole);

        // 👑 OWNER
        $owner = User::firstOrCreate(
            ['email' => 'owner@demo.com'],
            [
                'name' => 'Company Owner',
                'password' => Hash::make('123456'),
                'company_id' => $company->id,
            ]
        );
        $owner->assignRole($ownerRole);

        // 🧑‍💼 ADMIN
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('123456'),
                'company_id' => $company->id,
            ]
        );
        $admin->assignRole($adminRole);

        // 👥 USERS
        $users = [
            ['name' => 'User One', 'email' => 'user1@demo.com'],
            ['name' => 'User Two', 'email' => 'user2@demo.com'],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('123456'),
                    'company_id' => $company->id,
                ]
            );

            $user->assignRole($userRole);
        }
    }
}
