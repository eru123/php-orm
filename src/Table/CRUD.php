<?php

namespace eru123\orm\Table;

use Exception;
use eru123\orm\Raw;

trait CRUD
{
    use Base;

    /**
     * Insert Data to Table
     * @param array $data
     * @return int|bool
     */
    public function insert(array $data)
    {
        $data = static::to_many_data($data);

        $sql = $this->sqlInsertQuery($data);
        return $this->orm()->exec($sql)->pdo->lastInsertId();
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

        $columns = array_map(function ($value) {
            return "`$value`";
        }, $fillable);

        $rows = [];
        foreach ($data as $row) {
            $values = [];
            foreach ($fillable as $column) {
                if (isset($row[$column]) && $row[$column] instanceof Raw) {
                    $values[] = $row[$column];
                    continue;
                }

                if (!isset($row[$column]) || is_null($row[$column])) {
                    $values[] = 'NULL';
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