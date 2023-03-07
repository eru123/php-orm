<?php

namespace eru123\orm\ORM;

use Exception;

trait Model
{
    use Base;
    
    /**
     * Models
     * @var array
     */
    protected $models = [];

    /**
     * Add new Model to ORM
     * @param string $name
     * @param string $class
     * @return static
     */
    public function add(string $name, string $class)
    {
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
     * Get a Model from ORM
     */
    public function model(string $name)
    {
        return $this->__call($name, []);
    }
}