<?php

declare(strict_types=1);

namespace OllieCodes\Toolkit\Identity;

use Illuminate\Database\Eloquent\Model;

/**
 * Model Identity
 *
 * This class serves as the representation of a given a model instance. Its
 * responsibility is to convert an instance into a string that represents it. By
 * default, this will use the following pattern {connection}:{class}:{key}.
 *
 * These identities are used as keys when storing the model instances.
 */
class ModelIdentity
{
    /**
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \OllieCodes\Toolkit\Identity\ModelIdentity
     */
    public static function for(Model $model): ModelIdentity
    {
        if (method_exists($model, 'getModelIdentity')) {
            return $model->getModelIdentity();
        }

        return new self(
            $model::class,
            $model->getKey(),
            $model->getConnectionName()
        );
    }

    /**
     * @var string
     */
    protected string $class;

    /**
     * @var mixed
     */
    protected mixed $key;

    /**
     * @var string|null
     */
    protected ?string $connection = null;

    /**
     * @param string      $class
     * @param             $id
     * @param string|null $connection
     */
    public function __construct(string $class, $id, ?string $connection = null)
    {
        $this->key   = $id;
        $this->class = $class;
        $this->connection = $connection;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return mixed
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * @return string|null
     */
    public function getConnection(): ?string
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return implode(':', [
            $this->getConnection(),
            $this->getClass(),
            $this->getKey(),
        ]);
    }
}