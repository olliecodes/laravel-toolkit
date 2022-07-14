<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit\Identity\Facades;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Facade;
use OllieCodes\Toolkit\Identity\IdentityManager;
use OllieCodes\Toolkit\Identity\ModelIdentity;

/**
 * Identity Facade
 *
 * @method static IdentityManager getInstance()
 * @method static bool hasIdentity(ModelIdentity $identity)
 * @method static Model|null getIdentity(ModelIdentity $identity)
 * @method static IdentityManager storeIdentity(ModelIdentity $identity, Model $model)
 * @method static IdentityManager removeIdentity(ModelIdentity $identity)
 * @method static array allIdentities()
 */
class Identity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'toolkit.identity';
    }
}