<?php

namespace App\Http\Middleware;

use App\Fortify\Featurify as Features;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Permission;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'jetstream' => function () use ($request) {
                $user = $request->user();

                return [
                    'canCreateTeams' => $user &&
                                        Jetstream::userHasTeamFeatures($user) &&
                                        Gate::forUser($user)->check('create', Jetstream::newTeamModel()),
                    'canManageTwoFactorAuthentication' => Features::canManageTwoFactorAuthentication(),
                    'canUpdatePassword' => Features::enabled(Features::updatePasswords()),
                    'canUpdateProfileInformation' => Features::canUpdateProfileInformation(),
                    'hasEmailVerification' => Features::enabled(Features::emailVerification()),
                    'hasPhoneVerification' => Features::enabled(Features::phoneVerification()),
                    'flash' => $request->session()->get('flash', []),
                    'hasAccountDeletionFeatures' => Jetstream::hasAccountDeletionFeatures(),
                    'hasApiFeatures' => Jetstream::hasApiFeatures(),
                    'hasTeamFeatures' => Jetstream::hasTeamFeatures(),
                    'hasTermsAndPrivacyPolicyFeature' => Jetstream::hasTermsAndPrivacyPolicyFeature(),
                    'managesProfilePhotos' => Jetstream::managesProfilePhotos(),
                ];
            },
            'auth' => [
                'user' => function () use ($request) {
                    if (! $user = $request->user()) {
                        return;
                    }

                    $userHasTeamFeatures = Jetstream::userHasTeamFeatures($user);

                    if ($user && $userHasTeamFeatures) {
                        $user->currentTeam;
                    }

                    $availablePermissions = Permission::get();
                    $userPermissions = $user->getPermissionsViaRoles()->pluck('name');

                    $permissions = $availablePermissions->pluck('name')->mapWithKeys(function ($item) use ($user, $userPermissions) {
                        return [$item => $userPermissions->contains($item) || $user->hasRole('super_admin')];
                    });

                    return array_merge($user->toArray(), array_filter([
                        'all_teams' => $userHasTeamFeatures ? $user->allTeams()->values() : null,
                        'permissions' => $permissions,
                    ]), [
                        'two_factor_enabled' => ! is_null($user->two_factor_secret),
                    ]);
                },
            ],
            'custom' => [
                'jetstream' => function () {
                    return [
                        'hasPhoneVerification' => Features::enabled(Features::phoneVerification()),
                        'hasChangeThemeFeatures' => Features::enabled(Features::changeTheme()),
                    ];
                },
            ],
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
