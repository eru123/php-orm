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
}