<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit\Routing\Concerns;

use Illuminate\Contracts\Routing\Registrar;
use OllieCodes\Toolkit\Routing\Contracts\RouteRegistrar;
use RuntimeException;

/**
 * Maps Route Registrars Trait
 *
 * A helper trait to be added to any class that needs to map {@see \OllieCodes\Toolkit\Routing\Contracts\RouteRegistrar}
 * instances.
 */
trait MapsRouteRegistrars
{
    /**
     * Map the routes.
     *
     * @param \Illuminate\Contracts\Routing\Registrar                       $router
     * @param class-string<\OllieCodes\Toolkit\Routing\Contracts\RouteRegistrar>[] $registrars
     *
     * @return void
     */
    protected function mapRoutes(Registrar $router, array $registrars): void
    {
        foreach ($registrars as $registrar) {
            if (! class_exists($registrar) || ! is_subclass_of($registrar, RouteRegistrar::class)) {
                throw new RuntimeException(sprintf(
                    'Cannot map routes \'%s\', it is not a valid routes class',
                    $registrar
                ));
            }

            (new $registrar)->map($router);
        }
    }
}