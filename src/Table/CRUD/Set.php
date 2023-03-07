<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Raw;
use eru123\orm\Table\Base;
use Exception;

trait Set {
    use Base;

    /**
     * @var array
     */
    protected $set = [];

    /**
     * Set the fields to update
     * @param mixed $args
     * @return static
     */
    public function set(...$args): self
    {   
        if (count($args) === 1 && is_array($args[0])) {
            $this->set = array_merge($this->set, $args[0]);
        } else if (count($args) === 2) {
            $this->set[$args[0]] = $args[1];
        } else {
            throw new Exception('Invalid set arguments');
        }

        return $this;
    }

    /**
     * Get SET SQL Query String
     * @return Raw
     */
    protected function sqlSetQuery()
    {
        $set = [];
        foreach ($this->set as $column => $value) {
            $set[] = static::raw("`$column` = ?", [$value]);
        }

        return static::raw(implode(', ', $set));
    }

}