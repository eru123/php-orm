<?php

namespace eru123\orm;

use Exception;
use PDO;

class ORM
{
    protected $pdo;
    protected $models = [];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Use a PDO instance or create a new one
     * @param mixed ...$args
     * @return static
     */
    public static function use(...$args)
    {
        if (count($args) < 1) {
            throw new Exception("No arguments passed to " . static::class . "::use()");
        }

        if ($args[0] instanceof PDO) {
            return new static($args[0]);
        }

        $pdo = new PDO(...$args);
        return new static($pdo);
    }

    /**
     * Get the PDO instance
     * @return PDO
     */
    public function pdo()
    {
        return $this->pdo;
    }

    /**
     * Add new Model to ORM
     * @param string $name
     * @param string $class
     * @return static
     */
    public function add(string $name, string $class)
    {
        if (!is_subclass_of($class, Model::class)) {
            throw new Exception("Class $class is not a subclass of " . Model::class);
        }

        $this->models[$name] = $class;
        return $this;
    }

    /**
     * Get a Model from ORM Magic
     * @param string $name
     * @return Model
     */
    public function __get(string $name)
    {
        if (!isset($this->models[$name])) {
            throw new Exception("Model $name not found");
        }

        return new $this->models[$name]($this);
    }

    /**
     * Get a Model from ORM Magic
     * @param string $name
     * @param array $args
     * @return Model
     */
    public function __call(string $name, array $args)
    {
        if (!isset($this->models[$name])) {
            throw new Exception("Model $name not found");
        }

        return new $this->models[$name]($this, ...$args);
    }

    /**
     * Migrate all Models
     * @return static
     */
    public function migrate()
    {
        foreach ($this->models as $model) {
            $model::migrate($this);
        }

        return $this;
    }
}