<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit\Identity\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Date;
use LogicException;
use OllieCodes\Toolkit\Identity\Facades\Identity;
use OllieCodes\Toolkit\Identity\ModelIdentity;
use OllieCodes\Toolkit\Identity\QueryBuilder;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasMappableIdentity
{
    /**
     * Boots the trait.
     */
    public static function bootHasMappableIdentity(): void
    {
        // Add a deleted event so the identity is removed from the map
        static::deleted(
            fn(Model $model) => Identity::removeIdentity($model->getModelIdentity()));
        // Add a created event so newly created models are stored
        static::created(fn(Model $model) => Identity::storeIdentity($model->getModelIdentity(), $model));
    }

    /**
     * Check if the provided attributes are newer.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $attributes
     *
     * @return bool
     */
    protected function areAttributesMoreRecent(Model $model, array $attributes): bool
    {
        if (! $this->usesTimestamps()) {
            return true;
        }

        $updatedAt = $attributes[$this->getUpdatedAtColumn()];

        if ($updatedAt !== null) {
            $format = $this->getDateFormat();

            if (is_numeric($updatedAt)) {
                $updatedAt = Date::createFromTimestamp($updatedAt);
            } else if (Date::hasFormat($updatedAt, $format)) {
                $updatedAt = Date::createFromFormat($format, $updatedAt);
            }

            return $model->getAttribute($this->getUpdatedAtColumn())->isBefore($updatedAt);
        }

        return true;
    }

    /**
     * Get the model identity.
     *
     * @param null        $id
     * @param string|null $connection
     *
     * @return \OllieCodes\Toolkit\Identity\ModelIdentity
     */
    public function getModelIdentity($id = null, ?string $connection = null): ModelIdentity
    {
        $connection =
            $connection ?? $this->getConnectionName() ?? static::getConnectionResolver()->getDefaultConnection();

        return new ModelIdentity(static::class, $id ?? $this->getKey(), $connection);
    }

    /**
     * Get a relationship value from a method.
     *
     * @param string $method
     *
     * @return mixed
     *
     * @throws \LogicException
     */
    protected function getRelationshipFromMethod($method): mixed
    {
        $relation = $this->$method();

        if (! $relation instanceof Relation) {
            if (is_null($relation)) {
                throw new LogicException(sprintf(
                    '%s::%s must return a relationship instance, but "null" was returned. Was the "return" keyword used?', static::class, $method
                ));
            }

            throw new LogicException(sprintf(
                '%s::%s must return a relationship instance.', static::class, $method
            ));
        }

        return tap($this->getRelationshipResults($relation), function ($results) use ($method) {
            $this->setRelation($method, $results);
        });
    }

    protected function getRelationshipResults(Relation $relation)
    {
        if ($relation instanceof BelongsToMany) {
            return $relation->getResults();
        }

        if ($relation instanceof BelongsTo) {
            $related = $relation->getRelated();

            if (method_exists($related, 'getModelIdentity')) {
                $identity = $related->getModelIdentity($this->getAttribute($relation->getForeignKeyName()));

                if (Identity::hasIdentity($identity)) {
                    return Identity::getIdentity($identity);
                }
            }
        }

        return $relation->getResults();
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \OllieCodes\Toolkit\Identity\QueryBuilder
     */
    public function newEloquentBuilder($query): QueryBuilder
    {
        return new QueryBuilder($query);
    }

    /**
     * Override the default newFromBuilder method to use the identity map.
     *
     * @param array $attributes
     * @param null  $connection
     *
     * @return \Illuminate\Database\Eloquent\Model
     * @see    \Illuminate\Database\Eloquent\Model::newFromBuilder()
     *
     */
    public function newFromBuilder($attributes = [], $connection = null): Model
    {
        $attributes = (array)$attributes;
        $key        = $attributes[$this->getKeyName()] ?? null;
        $identity   = null;

        if ($key !== null) {
            $identity = $this->getModelIdentity($key, $connection);

            if (Identity::hasIdentity($identity)) {
                $model = Identity::getIdentity($identity);
                /** @noinspection NullPointerExceptionInspection */
                $this->updateModelAttributes($model, $attributes);

                return $model;
            }
        }

        $model = parent::newFromBuilder($attributes, $connection);

        if ($identity !== null) {
            Identity::storeIdentity($model->getModelIdentity(), $model);
        }

        return $model;
    }

    /**
     * Change the original attributes to match the new attributes, and re-add the dirty records.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $attributes
     */
    protected function updateModelAttributes(Model $model, array $attributes = []): void
    {
        if (! $this->areAttributesMoreRecent($model, $attributes)) {
            return;
        }

        $dirtyAttributes = $model->getDirty();
        $model->setRawAttributes($attributes, true);
        $model->setRawAttributes(array_merge($model->getAttributes(), $dirtyAttributes), false);
    }
}