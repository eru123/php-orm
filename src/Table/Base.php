<?php

namespace eru123\orm\Table;

use eru123\orm\Helper;
use eru123\orm\ORM;

trait Base
{
    use Helper;

    /**
     * @var ORM
     */
    protected $orm = null;

    /**
     * @var string
     */
    protected $table = null;

    /**
     * @var ?string
     */
    protected $primary_key = null;

    /**
     * @var array
     */
    protected $fields = [];

    /**
     * @var array
     */
    protected $indexes = [];

    public function __construct(ORM $orm)
    {
        $this->orm = $orm;
    }

    /**
     * Get the ORM instance
     */
    public function orm(): ORM
    {
        return $this->orm;
    }

    /**
     * Get primary key field name
     */
    public function primary_key(): ?string
    {
        return $this->primary_key;
    }

    /**
     * Get table name
     */
    public function table(): string
    {
        return $this->table;
    }

    /**
     * Get all fields
     */
    public function fields(): array
    {
        return array_keys($this->fields);
    }
}