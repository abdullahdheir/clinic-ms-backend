<?php

namespace App\Providers;

use App\Models\Visit;
use App\Observers\VisitObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
    public function boot(): void
    {
        Visit::observe(VisitObserver::class);
        
        Gate::define('viewFilament', function ($user) {
            return $user->hasRole('super_admin');
        });
    }
}
