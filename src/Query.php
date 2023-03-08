<?php

namespace eru123\orm;

use PDO;
use PDOStatement;

/**
 * Container for SQL Prepared Statement, PDO and SQL Query 
 */
class Query
{

    public $sql;
    public $pdo;
    public $stmt;

    public function __construct(string|Raw $sql, PDO $pdo, PDOStatement $stmt)
    {
        $this->sql = $sql;
        $this->pdo = $pdo;
        $this->stmt = $stmt;
    }

    public function toArray(): array
    {
        return [
            'sql' => $this->sql,
            'pdo' => $this->pdo,
            'stmt' => $this->stmt,
        ];
    }
}