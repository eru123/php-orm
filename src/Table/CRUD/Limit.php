<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Raw;

trait Limit
{

    /**
     * Limit
     * @var ?int
     */
    protected $limit = null;

    /**
     * Offset
     * @var ?int
     */
    protected $offset = null;

    /**
     * Set the limit
     * @param int $limit
     * @return static
     */
    public function limit(int $limit, ?int $offset = null)
    {
        $this->limit = $limit;
        $this->offset = $offset !== null ? $offset : $this->offset;
        return $this;
    }

    /**
     * Set the offset
     * @param int $offset
     * @return static
     */
    public function offset(int $offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Get LIMIT SQL Query String
     * @return Raw
     */
    protected function sqlLimitQuery()
    {
        if ($this->limit === null) {
            return static::raw('');
        }

        $limit = static::raw('LIMIT ?', [$this->limit]);
        if ($this->offset !== null) {
            $limit = static::raw('LIMIT ?, ?', [$this->offset, $this->limit]);
        }

        return $limit;
    }
}