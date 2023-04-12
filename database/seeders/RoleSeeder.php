<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Exception;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roles = collect([
            [
                "name"  => "super_admin"
            ],
            [
                "name"  => "admin"
            ],
            [
                "name"  => "demo"
            ],
            [
                "name"  => "member"
            ],
            [
                "name"  => "user"
            ],
            [
                "name"  => "guest"
            ],
        ]);

        try {
            DB::beginTransaction();

            $roles->each(function($role) {
                $role = Role::firstOrCreate($role, $role);

                switch ($role->name) {
                    case 'super_admin':
                        break;
                    case 'admin':
                        $role->givePermissionTo([
                            'canViewWelcome',
                            'canViewDashboard',
                            'canViewProfile',
                            'canUpdateProfileInformation',
                            'canUpdatePassword',
                            'canManageTwoFactorAuthentication',
                            'canUseAccountDeletionFeatures',
                            'canUseTeamFeatures',
                            'canUseApiFeatures'
                        ]);
                        break;
                    case 'demo':
                        $role->givePermissionTo([
                            'canViewWelcome',
                            'canViewDashboard',
                            'canViewProfile',
                            'canUseTeamFeatures',
                            'canUseApiFeatures'
                        ]);
                        break;
                    case 'member':
                        $role->givePermissionTo([
                            'canViewWelcome',
                            'canViewDashboard',
                            'canViewProfile',
                            'canUpdateProfileInformation',
                            'canUpdatePassword',
                            'canManageTwoFactorAuthentication',
                            'canUseAccountDeletionFeatures',
                            'canUseTeamFeatures',
                            'canUseApiFeatures'
                        ]);
                    case 'user':
                        $role->givePermissionTo([
                            'canViewWelcome',
                            'canViewDashboard',
                            'canViewProfile',
                            'canUpdateProfileInformation',
                            'canUpdatePassword',
                            'canManageTwoFactorAuthentication',
                            'canUseAccountDeletionFeatures',
                        ]);
                        break;
                    case 'guest':
                        $role->givePermissionTo([
                            'canViewWelcome',
                            'canViewDashboard',
                            'canViewProfile',
                        ]);
                        break;
                    default:
                        break;
                }
            });

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
}
