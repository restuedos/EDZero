<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = collect([
            [
                "name"              => "Restu Edo Setiaji",
                "email"             => "restuedosetiaji@gmail.com",
                "email_verified_at" => Carbon::now(),
                "password"          => Hash::make("Edo998877!"),
                "role"              => 'super_admin',
            ],
            [
                "name"              => "Super Admin",
                "email"             => "superadmin@edzero.co.id",
                "email_verified_at" => Carbon::now(),
                "password"          => Hash::make("superadmin"),
                "role"              => 'super_admin',
            ],
            [
                "name"              => "Admin",
                "email"             => "admin@edzero.co.id",
                "email_verified_at" => Carbon::now(),
                "password"          => Hash::make("admin"),
                "role"              => 'admin',
            ],
            [
                "name"              => "Demo",
                "email"             => "demo@edzero.co.id",
                "email_verified_at" => Carbon::now(),
                "password"          => Hash::make("demo"),
                "role"              => 'demo',
            ],
            [
                "name"              => "Guest",
                "email"             => "guest@edzero.co.id",
                "email_verified_at" => Carbon::now(),
                "password"          => Hash::make("guest"),
                "role"              => 'guest',
            ]
        ]);

        try {
            DB::beginTransaction();

            $users->each(function ($user) {
                $user = collect($user);

                $role = $user['role'];
                $newUser = User::firstOrCreate(
                    (clone $user)->only('email')->toArray(),
                    (clone $user)->except('role')->toArray()
                );
                $newUser->assignRole($role);
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
