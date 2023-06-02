<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = collect([
            [
                'name' => 'Restu Edo Setiaji',
                'username' => 'EDZero',
                'email' => 'restuedosetiaji@gmail.com',
                'email_verified_at' => Carbon::now(),
                'phone' => '895341028697',
                'phone_verified_at' => Carbon::now(),
                'password' => Hash::make('Edo998877!'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Super Admin',
                'username' => 'super_admin',
                'email' => 'superadmin@edzero.co.id',
                'email_verified_at' => Carbon::now(),
                'phone' => '81234567890',
                'phone_verified_at' => Carbon::now(),
                'password' => Hash::make('superadmin'),
                'role' => 'super_admin',
            ],
            [
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@edzero.co.id',
                'email_verified_at' => Carbon::now(),
                'phone' => '81234567891',
                'phone_verified_at' => Carbon::now(),
                'password' => Hash::make('admin'),
                'role' => 'admin',
            ],
            [
                'name' => 'Demo',
                'username' => 'demo',
                'email' => 'demo@edzero.co.id',
                'email_verified_at' => Carbon::now(),
                'phone' => '81234567892',
                'phone_verified_at' => Carbon::now(),
                'password' => Hash::make('demo'),
                'role' => 'demo',
            ],
            [
                'name' => 'Guest',
                'username' => 'guest',
                'email' => 'guest@edzero.co.id',
                'email_verified_at' => Carbon::now(),
                'phone' => '81234567893',
                'phone_verified_at' => Carbon::now(),
                'password' => Hash::make('guest'),
                'role' => 'guest',
            ],
        ]);

        try {
            DB::beginTransaction();

            $users->each(function ($user) {
                $user = collect($user);

                $role = $user['role'];
                $newUser = User::firstOrCreate(
                    (clone $user)->only(['email', 'username', 'phone'])->toArray(),
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
