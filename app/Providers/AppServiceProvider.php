<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

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
        // WAMP MySQL utf8mb4 index limit (1000 bytes) workaround.
        Schema::defaultStringLength(191);

        Model::preventLazyLoading(! $this->app->isProduction());

        // Laravel's default paginator markup is Tailwind. This project is
        // Bootstrap 5, so the Tailwind classes did nothing and the paginator's
        // inline SVG arrows rendered at full natural size — the oversized
        // chevrons on the collection page.
        Paginator::useBootstrapFive();

        // Applies everywhere Password::defaults() is referenced (registration,
        // password reset, change-password, admin user create/update).
        Password::defaults(function () {
            $rule = Password::min(8)->mixedCase()->numbers();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
