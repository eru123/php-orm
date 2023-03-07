<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Raw;
use eru123\orm\Table\Base;

trait Delete
{
    use Base;
    use Where;

    /**
     * Delete a row in the table
     * @var mixed $args Column name, value, operator, or Raw object
     * @return int Rows affected
     */
    public function delete(...$args): int
    {
        if (!empty($args)) {
            $this->where(...$args);
        }
        
        $sql = $this->sqlDeleteQuery();
        $query = $this->orm()->exec($sql);

        $this->where = [];
        return $query->stmt->rowCount();
    }

    /**
     * Delete SQL Query
     * @return Raw
     */
    public function sqlDeleteQuery()
    {
        $where = $this->sqlWhereQuery();
        return static::raw("DELETE FROM `?` WHERE ?", [static::raw($this->table), $where]);
    }
}