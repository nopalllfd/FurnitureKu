<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    // app/Providers/AuthServiceProvider.php
public function boot(): void
{
    Gate::define('is-admin', function (User $user) {
        return $user->role === 'admin';
    });

    Gate::define('is-buyer', function (User $user) {
        return $user->role === 'buyer';
    });
}
}
