<?php

namespace eru123\orm\Table\CRUD;

use Exception;
use eru123\orm\Table\Base;

trait Insert
{   
    use Base;
    use Callback;

    /**
     * Insert Data to Table
     * @param array $data
     * @return int|bool
     */
    public function insert(array $data)
    {
        $data = static::to_many_data($data);
        
        foreach ($data as $key => $row) {
            $data[$key] = $this->beforeInsert($row);
        }

        $sql = $this->sqlInsertQuery($data);
        $query = $this->orm()->exec($sql);
        $this->afterInsert($data, $query);
        
        if (count($data) === 1 && !empty($this->primary_key)) {
            return $query->pdo->lastInsertId();
        }

        if (count($data) > 1 && !empty($this->primary_key)) {
            return $query->stmt->rowCount();
        }

        return $query->stmt->rowCount() > 0;
    }

    /**
     * Insert Query Data to Table
     * @param array $data
     * @return string SQL Query String
     */
    public function sqlInsertQuery(array $data)
    {
        if (empty($data) || count($data) === 0 || !is_array($data[0])) {
            throw new Exception('No data to insert');
        }


        $fillable = array_keys($this->fields);
        $columns = [];
        $defaults = [];

        foreach ($fillable as $column) {
            $default = isset($this->fields[$column]['default']) ? $this->fields[$column]['default'] : 'NULL';
            $defaults[$column] = static::raw($default);
            $columns[] = "`$column`";
        }

        $rows = [];
        foreach ($data as $row) {
            $values = [];
            foreach ($fillable as $column) {
                if (isset($row[$column]) && $row[$column] instanceof Raw) {
                    $values[] = $row[$column];
                    continue;
                }

                if (!isset($row[$column]) || is_null($row[$column])) {
                    $values[] = $defaults[$column];
                    continue;
                }

                $values[] = static::raw('?', [$row[$column]]);
            }
            $rows[] = '(' . implode(', ', $values) . ')';
        }

        $rows_sql = implode(', ', $rows);
        $columns_sql = implode(', ', $columns);

        return "INSERT INTO `{$this->table}` ({$columns_sql}) VALUES {$rows_sql}";
    }
}