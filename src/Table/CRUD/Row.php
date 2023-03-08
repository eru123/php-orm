<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Model;
use eru123\orm\ORM;
use eru123\orm\Raw;
use Exception;

class Row
{
    protected $data = [];
    protected $changes = [];

    protected $selector = null;
    protected $model = null;

    public function __construct($model, $data = [])
    {
        $this->model = $model;
        $this->data = $data;

        $pk = $this->model->primary_key();
        if ($pk && isset($data[$pk])) {
            $this->selector = Raw::build("`?` = ?", [Raw::build($pk), $data[$pk]]);
        }
    }

    public function orm(): ORM
    {
        return $this->model->orm();
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function __get($name)
    {
        if (isset($this->changes[$name])) {
            return $this->changes[$name];
        }

        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->changes[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->data[$name]) || isset($this->changes[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
        unset($this->changes[$name]);
    }

    /**
     * Insert or update the row
     * @return bool
     */
    public function save()
    {
        $ins_w_pk = $this->model()->primary_key() && is_null($this->selector);
        $ins_wo_pk = !$this->model()->primary_key() && count($this->data) === 0;

        if ($ins_w_pk || $ins_wo_pk) {
            return $this->insert();
        }

        return $this->update();
    }

    /**
     * Insert the row
     * @return bool
     */
    public function insert()
    {
        $insert = $this->model->insert($this->changes);

        $pk = $this->model->primary_key();

        $insert_w_pri = $pk && is_int($insert) && $insert > 0;
        $insert_wo_pri = !$pk && $insert === true;

        if ($insert_w_pri) {
            $this->selector = Raw::build("`?` = ?", [Raw::build($pk), $insert]);
            $this->data[$pk] = $insert;
        }

        if ($insert_w_pri || $insert_wo_pri) {
            $this->data = $this->changes;
            $this->changes = [];
        }

        return false;
    }

    /**
     * Update the row
     * @return bool
     */
    public function update()
    {
        if (count($this->changes) == 0) {
            return false;
        }

        if (!$this->model()->primary_key()) {
            throw new Exception('Updating with row without primary key is not supported');
        }

        if (is_null($this->selector)) {
            throw new Exception('Run save() or insert() before update()');
        }

        $this->model()->where($this->selector)->update($this->changes);
        $this->data = array_merge($this->data, $this->changes);
        $this->changes = [];

        return true;
    }

    /**
     * Delete the row
     * @return bool
     */
    public function delete()
    {
        if (!$this->model()->primary_key()) {
            throw new Exception('Updating with row without primary key is not supported');
        }

        if (is_null($this->selector)) {
            throw new Exception('Run save() or insert() before delete()');
        }

        $this->model()->where($this->selector)->delete();
        $this->data = [];
        $this->changes = [];
        $this->selector = null;

        return true;
    }

    /**
     * Get the row as array
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->data, $this->changes);
    }

    /**
     * Check if the row exists
     * @return bool
     */
    public function exists()
    {   
        $pk = $this->model->primary_key();
        $pk_n_exists = $pk && !empty($this->data[$pk]) && !empty($this->selector);
        $pk_n_exists_wo_pk = !$pk && count($this->data) > 0 && !empty($this->selector);
        return $pk_n_exists || $pk_n_exists_wo_pk;
    }
}