<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::before(function ($user, $ability) {
            if (in_array($ability, ['backup', 'superadmin',
                'manage_modules', ])) {
                $administrator_list = config('constants.administrator_usernames');

                if (in_array(strtolower($user->username), explode(',', strtolower($administrator_list)))) {
                    return true;
                }
            } else {
                // Allow any role named 'Admin' or starting with 'Admin#' (case-insensitive)
                $roleNames = $user->roles ? $user->roles->pluck('name')->map(function ($n) { return strtolower(trim($n)); }) : collect();
                $hasAdminLikeRole = $roleNames->contains('admin') || $roleNames->first(function ($n) { return Str::startsWith($n, 'admin#'); });
                if ($hasAdminLikeRole) {
                    return true;
                }
                // Fallback to common explicit checks
                if ($user->hasRole('Admin#'.$user->business_id) || $user->hasRole('Admin')) {
                    return true;
                }
            }
        });
    }
}
