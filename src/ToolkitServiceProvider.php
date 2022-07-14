<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit;

use Illuminate\Support\ServiceProvider;
use OllieCodes\Toolkit\Identity\IdentityToolkitServiceProvider;
use OllieCodes\Toolkit\Routing\RoutingToolkitServiceProvider;

class ToolkitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (Features::hasRouting()) {
            $this->app->register(RoutingToolkitServiceProvider::class);
        }

        if (Features::hasIdentityMapping()) {
            $this->app->register(IdentityToolkitServiceProvider::class);
        }
    }

    public function boot(): void
    {
        $this->publishConfig();
    }

    private function publishConfig(): void
    {
        $this->publishes([
            __DIR__ . '/../config/toolkit.php' => config_path('toolkit.php'),
        ], ['config', 'toolkit']);
    }
}