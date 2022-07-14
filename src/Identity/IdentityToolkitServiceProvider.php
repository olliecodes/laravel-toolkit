<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit\Identity;

use Illuminate\Support\ServiceProvider;

class IdentityToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IdentityManager::class,
            fn() => IdentityManager::getInstance(),
            true
        );

        $this->app->alias(
            IdentityManager::class,
            'toolkit.identity'
        );
    }
}