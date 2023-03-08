<?php

namespace eru123\orm;

use Exception;

/**
 * Universal ORM Container
 */
class Uni
{
    private static $orms = [];
    private static $orm = null;

    /**
     * Create Global ORM Instance
     * @param   string  $name  Alias for ORM
     * @param   mixed   $args  PDO or PDO arguments
     * @return  ORM
     */
    public static function create($name, ...$args)
    {
        if (method_exists(static::class, $name) || property_exists(static::class, $name)) {
            throw new Exception("Cannot create ORM with name '$name' because it is a reserved name.");
        }

        $orm = ORM::create(...$args);
        self::$orms[$name] = $orm;
        if (is_null(self::$orm)) {
            self::$orm = $orm;
        }
        return $orm;
    }

    /**
     * Add ORM to Global Instance
     * @param   string  $name  Alias for ORM
     * @param   ORM     $orm   ORM Instance
     * @return  ORM
     */
    public static function add($name, ORM $orm)
    {
        if (method_exists(static::class, $name) || property_exists(static::class, $name)) {
            throw new Exception("Cannot add ORM with name '$name' because it is a reserved name.");
        }

        self::$orms[$name] = $orm;
        if (is_null(self::$orm)) {
            self::$orm = $orm;
        }

        return $orm;
    }

    /**
     * Get Global ORM Instance
     * @param   string  $name  Alias for ORM
     * @return  ORM
     */
    public static function get($name = null)
    {
        if (is_null($name)) {
            return self::$orm;
        }

        if (isset(self::$orms[$name])) {
            return self::$orms[$name];
        }

        throw new Exception("ORM with name '$name' does not exist.");
    }

    /**
     * Get Global ORM Instance statically
     * @param   string  $name  Alias for ORM
     * @return  ORM
     */
    public static function __callStatic($name, $args)
    {
        return self::get($name);
    }

    /**
     * Get Global ORM Instance dynamically
     * @param   string  $name  Alias for ORM
     * @return  ORM
     */
    public function __call($name, $args)
    {
        return self::get($name);
    }

    /**
     * Get Global ORM Instance using property
     * @param   string  $name  Alias for ORM
     * @return  ORM
     */
    public function __get($name)
    {
        return self::get($name);
    }

    /**
     * Default ORM
     * @return  ORM
     */
    public static function default()
    {
        if (is_null(self::$orm)) {
            throw new Exception("No default ORM has been set.");
        }

        return self::$orm;
    }

    /**
     * Set Default ORM
     * @param   ORM  $orm  ORM Instance
     * @return  ORM
     */
    public static function setDefault(ORM $orm)
    {
        self::$orm = $orm;
        return self::$orm;
    }

    /**
     * Create Default ORM
     * @param   mixed  $args  PDO or PDO arguments
     * @return  ORM
     */
    public static function createDefault(...$args)
    {
        self::$orm = ORM::create(...$args);
        return self::$orm;
    }
}