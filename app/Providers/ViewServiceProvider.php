<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\LayoutService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Global chrome (top bar / header / footer) is composed once per
     * request from the cached layout payload — frontend blades never query.
     */
    public function boot(): void
    {
        View::composer(
            [
                'frontend.partials.topbar', 'frontend.partials.header',
                'frontend.partials.footer',
            ],
            function (\Illuminate\View\View $view): void {
                $chrome = app(LayoutService::class)->renderPayload();

                $view->with('chrome', $chrome);
            }
        );
    }
}
