<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Repository interface → implementation bindings.
     *
     * Module repositories are added here as each module is built, e.g.:
     * \App\Repositories\Contracts\PageRepositoryInterface::class
     *     => \App\Repositories\Eloquent\PageRepository::class,
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        \App\Repositories\Contracts\SettingRepositoryInterface::class
            => \App\Repositories\Eloquent\SettingRepository::class,
        \App\Repositories\Contracts\UserRepositoryInterface::class
            => \App\Repositories\Eloquent\UserRepository::class,
        \App\Repositories\Contracts\RoleRepositoryInterface::class
            => \App\Repositories\Eloquent\RoleRepository::class,
        \App\Repositories\Contracts\MediaRepositoryInterface::class
            => \App\Repositories\Eloquent\MediaRepository::class,
        \App\Repositories\Contracts\SectionRepositoryInterface::class
            => \App\Repositories\Eloquent\SectionRepository::class,
        \App\Repositories\Contracts\MenuRepositoryInterface::class
            => \App\Repositories\Eloquent\MenuRepository::class,
        \App\Repositories\Contracts\PageRepositoryInterface::class
            => \App\Repositories\Eloquent\PageRepository::class,
        \App\Repositories\Contracts\PostRepositoryInterface::class
            => \App\Repositories\Eloquent\PostRepository::class,
        \App\Repositories\Contracts\ThemeRepositoryInterface::class
            => \App\Repositories\Eloquent\ThemeRepository::class,
        \App\Repositories\Contracts\GlobalBlockRepositoryInterface::class
            => \App\Repositories\Eloquent\GlobalBlockRepository::class,
        \App\Repositories\Contracts\GradientRepositoryInterface::class
            => \App\Repositories\Eloquent\GradientRepository::class,
        \App\Repositories\Contracts\ButtonStyleRepositoryInterface::class
            => \App\Repositories\Eloquent\ButtonStyleRepository::class,
        \App\Repositories\Contracts\DeveloperOverrideRepositoryInterface::class
            => \App\Repositories\Eloquent\DeveloperOverrideRepository::class,
    ];
}
