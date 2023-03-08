<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Model;
use eru123\orm\Raw;
use Exception;
use Iterator;
use Countable;

/**
 * Collection of Row
 */
class Rows implements Iterator, Countable
{
    /**
     * Model instance
     * @var ?Model
     */
    protected $model = null;

    /**
     * Data
     * @var array
     */
    protected $data = [];

    /**
     * Current position
     * @var int
     */
    protected $position = 0;

    /**
     * Selector for this collection
     * @var ?Raw
     */
    protected $selector = null;

    public function __construct($model, $data = [])
    {
        $this->model = $model;
        $this->data = [];

        foreach ($data as $value) {
            $this->data[] = new Row($this->model, $value);
        }

        $pk = $this->model->primary_key();
        if ($pk) {
            $ids = array_map(function ($row) use ($pk) {
                return $row[$pk];
            }, $data);

            $this->selector = Raw::build("`?` IN (" . implode(',', array_fill(0, count($ids), '?')) . ")", array_merge([Raw::build($pk)], $ids));
        }
    }

    /**
     * Get Model instance
     * @return Model
     */
    public function model()
    {
        return $this->model;
    }

    public function current(): mixed
    {
        return new Row($this->model, $this->data[$this->position]);
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function key(): mixed
    {
        return $this->position;
    }

    public function valid(): bool
    {
        return isset($this->data[$this->position]);
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get first row in collection
     * @return Row|null
     */
    public function first()
    {
        return $this->data[0] ?? null;
    }

    /**
     * Get last row in collection
     * @return Row|null
     */
    public function last()
    {
        return $this->data[count($this->data) - 1] ?? null;
    }

    /**
     * Get all rows as array
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($row) {
            return $row->toArray();
        }, $this->data);
    }

    /**
     * Delete all rows in collection
     * @return int Number of affected rows
     */
    public function delete()
    {
        $pk = $this->model->primary_key();
        if (!$pk) {
            throw new Exception('Cannot delete rows without primary key');
        }

        return $this->model()
            ->where($this->selector)
            ->delete();
    }

    /**
     * Update all rows in collection
     * @param array $data Data to update
     * @return int Number of affected rows
     */
    public function update($data)
    {
        $pk = $this->model->primary_key();
        if (!$pk) {
            throw new Exception('Cannot update rows without primary key');
        }

        return $this->model()
            ->set($data)
            ->where($this->selector)
            ->update();
    }

    /**
     * Refresh all rows in collection
     * @return static
     */
    public function refresh()
    {
        $pk = $this->model->primary_key();
        if (!$pk) {
            throw new Exception('Cannot refresh rows without primary key');
        }

        $data = $this->model()
            ->where($this->selector)
            ->get()
            ->toArray();

        $this->data = [];
        foreach ($data as $value) {
            $this->data[] = new Row($this->model, $value);
        }

        return $this;
    }
}