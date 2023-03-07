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

    public function sqlFindQuery()
    {
        $select = $this->sqlSelectQuery();
        $where = $this->sqlWhereQuery();
        $table = static::raw($this->table);
        return static::raw("SELECT ? FROM `?` WHERE ? LIMIT 1", [$select, $table, $where]);
    }

    public function sqlGetQuery()
    {
        $select = $this->sqlSelectQuery();
        $where = $this->sqlWhereQuery();
        $limit = $this->sqlLimitQuery();
        $table = static::raw($this->table);
        return static::raw("SELECT ? FROM `?` WHERE ? ?", [$select, $table, $where, $limit]);
    }

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

        return $query->stmt->fetch() ?: null;
    }

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

        return $query->stmt->fetchAll() ?: [];
    }
}