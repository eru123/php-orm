<?php

namespace eru123\orm;

use Exception;
use PDO;

class ORM
{
    protected $pdo = [];
    protected $models = [];

    protected $debug = false;
    protected $debug_stack = [];

    public function __construct(...$args)
    {
        if (empty($args)) {
            throw new Exception("No arguments passed to " . static::class . "::__construct()");
        }

        $this->pdo = $args[0] instanceof PDO ? $args[0] : new PDO(...$args);
    }

    public function debug(bool $debug = true)
    {
        $this->debug = $debug;
        return $this;
    }

    public function debug_stack(array $stack = [])
    {
        $this->debug_stack = $stack;
        return $this;
    }

    private function debug_info($msg, $color = 'white')
    {
        if ($this->debug) {
            $this->debug_stack[] = [
                'message' => $msg,
                'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
            ];
        }

        if (php_sapi_name() == 'cli' && is_string($msg) && !empty($msg)) {
            $colors = [
                'black' => '0;30',
                'dark_gray' => '1;30',
                'blue' => '0;34',
                'light_blue' => '1;34',
                'green' => '0;32',
                'light_green' => '1;32',
                'cyan' => '0;36',
                'light_cyan' => '1;36',
                'red' => '0;31',
                'light_red' => '1;31',
                'purple' => '0;35',
                'light_purple' => '1;35',
                'brown' => '0;33',
                'yellow' => '1;33',
                'light_gray' => '0;37',
                'white' => '1;37',
            ];

            if (isset($colors[$color])) {
                $msg = "\033[" . $colors[$color] . "m" . $msg . "\033[0m";
            }

            echo $msg, PHP_EOL;
        }
    }

    /**
     * Use a PDO instance or create a new one
     * @param mixed ...$args
     * @return static
     */
    public static function use (...$args)
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
            $this->pdo = new PDO(...$this->pdo);
        }

        if (!($this->pdo instanceof PDO)) {
            throw new Exception("No PDO instance found");
        }

        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $this->pdo;
    }

    /**
     * Add new Model to ORM
     * @param string $name
     * @param string $class
     * @return static
     */
    public function add(string $name, string $class)
    {
        if (!is_subclass_of($class, Model::class)) {
            throw new Exception("Class $class is not a subclass of " . Model::class);
        }

        $this->models[$name] = $class;
        return $this;
    }

    /**
     * Get a Model from ORM Magic
     * @param string $name
     * @return Model
     */
    public function __get(string $name)
    {
        if (!isset($this->models[$name])) {
            throw new Exception("Model $name not found");
        }

        return new $this->models[$name]($this);
    }

    /**
     * Get a Model from ORM Magic
     * @param string $name
     * @param array $args
     * @return Model
     */
    public function __call(string $name, array $args)
    {
        if (!isset($this->models[$name])) {
            throw new Exception("Model $name not found");
        }

        return new $this->models[$name]($this, ...$args);
    }

    /**
     * Get a Model from ORM
     */
    public function model(string $name)
    {
        return $this->__call($name, []);
    }

    /**
     * Execute a query
     */
    public function exec(string $query, array $params = [])
    {
        $this->debug_info($query, 'green');
        $pdo = $this->pdo();
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return new Query($query, $pdo, $stmt);
    }

    /**
     * Migrate all Models
     * @return static
     */
    public function migrate()
    {   
        $this->debug_info("Migrating all models", 'yellow');
        foreach (array_keys($this->models) as $model) {
            $this->debug_info(" --- $model", 'cyan');
            $this->__call($model, [])->migrate();
        }

        return $this;
    }

    /**
     * Drop all Models
     * @return static
     */
    public function drop()
    {
        $this->debug_info("Dropping all models", 'yellow');
        foreach (array_keys($this->models) as $model) {
            $this->debug_info(" --- $model", 'cyan');
            $this->__call($model, [])->drop();
        }

        return $this;
    }

    /**
     * Test connection
     * @return static
     */
    public function test()
    {
        $this->debug_info("Testing connection", 'cyan');
        $this->exec("SELECT 1");
        return $this;
    }
}