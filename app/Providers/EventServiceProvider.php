<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        $activity = fn () => $this->app->make(\App\Services\ActivityLogService::class);

        // Security audit trail — file (storage/logs/security-*.log, 90-day
        // retention, for ops tail -f) AND DB (activity_logs, for the admin
        // "Login Logs" screen) in parallel; neither replaces the other.
        Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) use ($activity): void {
            $email = $event->credentials['email'] ?? null;

            \Illuminate\Support\Facades\Log::channel('security')->info('Login failed', [
                'email' => $email,
                'ip' => request()->ip(),
            ]);

            $activity()->log('login_failed', $event->user?->id, extra: ['description' => $email]);
        });

        Event::listen(\Illuminate\Auth\Events\Lockout::class, function ($event) use ($activity): void {
            \Illuminate\Support\Facades\Log::channel('security')->warning('Login lockout', [
                'email' => $event->request->input('email'),
                'ip' => $event->request->ip(),
            ]);

            $activity()->log('lockout', null, $event->request, ['description' => $event->request->input('email')]);
        });

        Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) use ($activity): void {
            \Illuminate\Support\Facades\Log::channel('security')->info('Login success', [
                'user_id' => $event->user->getAuthIdentifier(),
                'ip' => request()->ip(),
            ]);

            $activity()->log('login', $event->user->getAuthIdentifier());
        });

        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) use ($activity): void {
            if ($event->user) {
                $activity()->log('logout', $event->user->getAuthIdentifier());
            }
        });
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
