<?php

namespace eru123\orm;

use PDO;
use PDOStatement;

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
}