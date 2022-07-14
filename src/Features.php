<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit;

final class Features
{
    /**
     * Determine if the given feature is enabled.
     *
     * @param  string  $feature
     * @return bool
     */
    public static function enabled(string $feature): bool
    {
        return in_array(
            $feature,
            config('toolkit.features', []),
            true
        );
    }

    /**
     * Determine if the application is using the routing feature.
     *
     * @return bool
     */
    public static function hasRouting(): bool
    {
        return self::enabled(self::routing());
    }

    /**
     * Determine if the application is using the routing feature.
     *
     * @return bool
     */
    public static function hasIdentityMapping(): bool
    {
        return self::enabled(self::identityMapping());
    }

    /**
     * Enable the routing feature.
     *
     * @return string
     */
    public static function routing(): string
    {
        return 'routing';
    }

    /**
     * Enable the eloquent identity feature.
     *
     * @return string
     */
    public static function identityMapping(): string
    {
        return 'identity-mapping';
    }
}