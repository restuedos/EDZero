<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
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

        $permissions = collect([
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

        $permissions = collect($permissions)->map(function ($permission) {
            return ['name' => $permission, 'guard_name' => 'web'];
        });
    
        Permission::upsert($permissions->toArray(), $permissions->keys());
    }
}
