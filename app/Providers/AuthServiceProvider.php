<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define a gate that allows only 'super admin'
        Gate::define('is-super-admin', function ($user) {
            return $user->hasRole('super admin');
        });
    }
}
