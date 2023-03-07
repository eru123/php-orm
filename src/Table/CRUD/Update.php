<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Raw;
use eru123\orm\Table\Base;

trait Update
{
    use Base;
    use Set;
    use Where;

    /**
     * Update a row in the table
     * @param array $data
     * @return int Rows affected
     */
    public function update(array $data = []): int
    {   
        $this->set($data);
        $this->set = $this->beforeUpdate($this->set);
        $sql = $this->sqlUpdateQuery($this->set);
        $query = $this->orm()->exec($sql);
        $this->set = [];
        $this->where = [];
        $this->afterUpdate($this->set, $query);
        return $query->stmt->rowCount();
    }

    /**
     * Update SQL Query
     * @param array $data
     * @return Raw
     */
    public function sqlUpdateQuery(array $data)
    {
        $this->set($data);
        $set = $this->sqlSetQuery();
        $where = $this->sqlWhereQuery();
        return static::raw("UPDATE `?` SET ? WHERE ?", [static::raw($this->table), $set, $where]);
    }

}