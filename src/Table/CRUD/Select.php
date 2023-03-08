<?php

namespace eru123\orm\Table\CRUD;

use PDO;
use eru123\orm\Raw;
use eru123\orm\Table\Base;

trait Select
{
    use Base;
    use Where;
    use Limit;

    /**
     * Selected columns
     * @var array
     */
    protected $select = [];

    /**
     * Select columns
     * @param  mixed ...$args Column names
     * @return static
     */
    public function select(...$args)
    {
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $this->select = array_merge($this->select, $arg);
            } else {
                $this->select[] = $arg;
            }
        }

        return $this;
    }

    /**
     * Get SELECT SQL Query String
     * @return Raw
     */
    protected function sqlSelectQuery()
    {
        $select = [];
        foreach ($this->select as $column) {
            if ($column instanceof Raw) {
                $select[] = $column;
            } else {
                $select[] = static::raw("`{$column}`");
            }
        }

        if (empty($select)) {
            $select[] = static::raw("*");
        }

        return static::raw(implode(", ", array_fill(0, count($select), "?")), $select);
    }

    /**
     * Get SELECT SQL Query String
     * @return Raw
     */
    public function sqlFindQuery()
    {
        $select = $this->sqlSelectQuery();
        $where = $this->sqlWhereQuery();
        $table = static::raw($this->table);
        return static::raw("SELECT ? FROM `?` WHERE ? LIMIT 1", [$select, $table, $where]);
    }

    /**
     * Get SELECT SQL Query String
     * @return Raw
     */
    public function sqlGetQuery()
    {
        $select = $this->sqlSelectQuery();
        $where = $this->sqlWhereQuery();
        $limit = $this->sqlLimitQuery();
        $table = static::raw($this->table);
        return static::raw("SELECT ? FROM `?` WHERE ? ?", [$select, $table, $where, $limit]);
    }

    /**
     * Find a row
     * @param  mixed ...$args Column names
     * @return Row
     */
    public function find(...$args)
    {
        if (count($args) > 0) {
            $this->select(...$args);
        }

        $sql = $this->sqlFindQuery();
        $query = $this->orm()->exec($sql);

        $this->select = [];
        $this->where = [];
        $this->limit = null;
        $this->offset = null;

        $result = $query->stmt->fetch() ?: [];
        return new Row($this, $result);
    }

    /**
     * Get multiple rows
     * @param  mixed ...$args Column names
     * @return Rows
     */
    public function get(...$args)
    {
        if (count($args) > 0) {
            $this->select(...$args);
        }

        $sql = $this->sqlGetQuery();
        $query = $this->orm()->exec($sql);

        $this->select = [];
        $this->where = [];
        $this->limit = null;
        $this->offset = null;

        $result = $query->stmt->fetchAll() ?: [];
        return new Rows($this, $result);
    }

    /**
     * Clear the select
     */
    protected function clearSelect()
    {
        $this->select = [];
    }

    /**
     * Clear the find
     */
    protected function clearFind()
    {
        $this->clearSelect();
        $this->clearWhere();
    }

    /**
     * Clear the get
     */
    protected function clearGet()
    {
        $this->clearSelect();
        $this->clearWhere();
        $this->clearLimit();
    }
}