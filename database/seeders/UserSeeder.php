<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure roles exist (in case they haven't been seeded yet)
        $superAdminRole = Role::firstOrCreate(['name' => 'super admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // --- SUPER ADMIN ---
        $superAdmin = User::firstOrCreate(
            ['email' => 'zeltacodeofficial@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('zeltacode@2025'),
                'email_verified_at' => now(),
            ]
        );
        $superAdmin->assignRole($superAdminRole);

        // --- ADMIN USER ---
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('admin@2025'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($userRole);

        // --- REGULAR USER ---
        $regularUser = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Regular User',
                'password' => Hash::make('user@2025'),
                'email_verified_at' => now(),
            ]
        );
        $regularUser->assignRole($userRole);

        // --- ALEX ---
        $alex = User::firstOrCreate(
            ['email' => 'alex@landysworldwide.com'],
            [
                'name' => 'Alex',
                'password' => Hash::make('alex@2025'),
                'email_verified_at' => now(),
            ]
        );
        $alex->assignRole($userRole);

        // --- INFO ACCOUNT ---
        $info = User::firstOrCreate(
            ['email' => 'info@landysworldwide.com'],
            [
                'name' => 'Info Account',
                'password' => Hash::make('info@2025'),
                'email_verified_at' => now(),
            ]
        );
        $info->assignRole($userRole);
    }
}
