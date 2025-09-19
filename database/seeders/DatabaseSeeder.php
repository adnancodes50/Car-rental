<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            // Create default admin user
            $user = \App\Models\User::create([
                'name' => 'Administrator',
                'email' => 'admin@local.test',
                'password' => Hash::make('password'), // 🔑 change later for security
                'email_verified_at' => Carbon::now(),
            ]);

            // Create administrator role
            $role = \App\Models\Role::create([
                'name' => 'administrator',
            ]);

            // ✅ Attach role to user (if you have user-role relation)
            if (method_exists($user, 'roles')) {
                $user->roles()->attach($role->id);
            }
        });

        // Run permissions seeder (if it exists)
        $this->call(PermissionSeeder::class);

        // ✅ Run vehicles seeder
        $this->call(VehicleSeeder::class);
    }
}
