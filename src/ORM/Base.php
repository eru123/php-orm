<?php

namespace eru123\orm\ORM;

use eru123\orm\Query;
use eru123\orm\Raw;
use Exception;
use PDO;

trait Base
{
    use Debug;

    /**
     * Execute a query
     */
    public function exec(string $query, array $params = [])
    {
        $sql = Raw::build($query, $params);
        $this->debug_info($sql, 'green');
        $pdo = $this->pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return new Query($sql, $pdo, $stmt);
    }

    protected $pdo = [];

    public function __construct(...$args)
    {
        if (empty($args)) {
            throw new Exception("No arguments passed to " . static::class . "::__construct()");
        }

        $this->pdo = $args[0] instanceof PDO ? $args[0] : new PDO(...$args);
    }

    /**
     * Use a PDO instance or create a new one
     * @param mixed ...$args PDO Instance or PDO Arguments
     * @return static
     */
    public static function create(...$args)
    {
        if (count($args) < 1) {
            throw new Exception("No arguments passed to " . static::class . "::use()");
        }

        return new static(...$args);
    }

    /**
     * Get the PDO instance
     * @return PDO
     */
    public function pdo()
    {
        if (!is_array($this->pdo) && !($this->pdo instanceof PDO)) {
            $this->pdo = new PDO(...(array) $this->pdo);
        }

        if (!($this->pdo instanceof PDO)) {
            throw new Exception("No PDO instance found");
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $this->pdo;
    }
}