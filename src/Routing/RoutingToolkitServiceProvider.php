<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit\Routing;

use Illuminate\Support\ServiceProvider;
use OllieCodes\Toolkit\Routing\Commands\InitRoutingCommand;
use OllieCodes\Toolkit\Routing\Commands\RouteRegistrarMakeCommand;

class RoutingToolkitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InitRoutingCommand::class,
                RouteRegistrarMakeCommand::class,
            ]);
        }
    }
}