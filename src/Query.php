<?php

namespace eru123\orm;

class Query {

    public $sql;
    public $pdo;
    public $stmt;

    public function __construct($sql, $pdo, $stmt) {
        $this->sql = $sql;
        $this->pdo = $pdo;
        $this->stmt = $stmt;
    }
}