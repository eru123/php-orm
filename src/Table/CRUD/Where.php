<?php

namespace eru123\orm\Table\CRUD;

use eru123\orm\Raw;
use eru123\orm\Table\Base;
use Exception;

trait Where
{
    use Base;

    protected $where = [];

    /**
     * Injects conditional operators into the array
     * @param   array   $where     Reference to the array
     * @param   string  $operator  Conditional operator
     * @return  static
     */
    public function condition(array &$where, string $operator = "AND")
    {
        if (count($where) > 0) {
            $where[] = $operator;
        }

        return $this;
    }

    /**
     * Where clause
     * @param   mixed  $args    Column name, value, operator, or Raw object
     * @return  static
     */
    public function where(...$args)
    {
        if (count($args) === 1 && $args[0] instanceof Raw) {
            $this->condition($this->where, "AND");
            $this->where[] = $args[0];
        } else if (count($args) === 1 && is_array($args[0])) {
            $this->condition($this->where, "AND");
            $this->where[] = $this->parse_where($args[0]);
        } else if (count($args) === 2) {
            $this->condition($this->where, "AND");
            $this->where[] = $this->parse_where([$args[0] => $args[1]]);
        } else if (count($args) === 3) {
            $this->condition($this->where, "AND");
            $this->where[] = $this->parse_where([$args[0] => [$args[1] => $args[2]]]);
        } else {
            throw new Exception('Invalid where arguments');
        }

        return $this;
    }

    /**
     * And Where clause
     * @param   mixed  $args    Column name, value, operator, or Raw object
     * @return  static
     */
    public function andWhere(...$args)
    {
        return $this->where(...$args);
    }

    /**
     * Or Where clause
     * @param   mixed  $args    Column name, value, operator, or Raw object
     * @return  static
     */
    public function orWhere(...$args)
    {
        if (count($args) === 1 && $args[0] instanceof Raw) {
            $this->condition($this->where, "OR");
            $this->where[] = $args[0];
        } else if (count($args) === 1 && is_array($args[0])) {
            $this->condition($this->where, "OR");
            $this->where[] = $this->parse_where($args[0]);
        } else if (count($args) === 2) {
            $this->condition($this->where, "OR");
            $this->where[] = $this->parse_where([$args[0] => $args[1]]);
        } else if (count($args) === 3) {
            $this->condition($this->where, "OR");
            $this->where[] = $this->parse_where([$args[0] => [$args[1] => $args[2]]]);
        } else {
            throw new Exception('Invalid where arguments');
        }

        return $this;
    }

    /**
     * Parse where array
     * @param   array  $where  Where array
     * @return  Raw
     */
    public function parse_where(array $where)
    {
        $conditions = [];

        $ops = [
            'gt' => '>',
            'gte' => '>=',
            'lt' => '<',
            'lte' => '<=',
            'ne' => '!=',
            'like' => 'LIKE',
            'not like' => 'NOT LIKE',
            'in' => 'IN',
            'not in' => 'NOT IN',
            'between' => 'BETWEEN',
            'not between' => 'NOT BETWEEN',
            'is' => 'IS',
            'is not' => 'IS NOT',
        ];

        foreach ($where as $col => $val) {
            if (!is_array($val)) {
                $conditions[] = static::raw("`$col` = ?", [$val]);
                continue;
            }

            foreach ($val as $op => $v) {
                $pre_op = str_replace('_', ' ', strtolower($op));

                if (isset($ops[$pre_op])) {
                    $op = $ops[$pre_op];
                }

                $op = strtoupper($op);

                if ($op === 'IN' || $op === 'NOT IN') {
                    if (!is_array($v) || count($v) === 0) {
                        throw new Exception('Invalid in arguments');
                    }
                    $conditions[] = static::raw("`$col` $op (" . implode(", ", array_fill(0, count($v), "?")) . ")", $v);
                } else if ($op === 'BETWEEN' || $op === 'NOT BETWEEN') {
                    if (!is_array($v) || count($v) !== 2) {
                        throw new Exception('Invalid between arguments');
                    }
                    $conditions[] = static::raw("`$col` $op ? AND ?", $v);
                } else {
                    if (!is_array($v)) {
                        $v = [$v];
                    } else {
                        $v = static::raw("(" . implode(", ", array_fill(0, count($v), "?")) . ")", $v);
                    }
                    $conditions[] = static::raw("`$col` $op ?", $v);
                }
            }
        }

        return count($conditions) > 1 ? static::raw("(" . implode(" AND ", array_fill(0, count($conditions), "?")) . ")", $conditions): $conditions[0];
    }

    /**
     * Get where SQL Query
     * @return  string
     */
    public function sqlWhereQuery()
    {
        if (count($this->where) === 0) {
            return static::raw("1");
        }

        return static::raw(implode(" ", array_fill(0, count($this->where), "?")), $this->where);
    }
}