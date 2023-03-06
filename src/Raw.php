<?php

namespace eru123\orm;

class Raw
{
    protected $query = null;

    public function __construct(protected string $sql, protected array $params = [])
    {
        if (!empty($params)) {
            $this->query = $sql;

            $tmp_params = [];
            if (array_keys($params) === range(0, count($params) - 1)) {
                foreach ($params as $index => $param) {
                    $param_key = ':p__' . $index;
                    $this->query = preg_replace('/\?/', $param_key, $this->query, 1);
                    $tmp_params[$param_key] = $param;
                }
                $params = &$tmp_params;
            }

            foreach ($params as $key => $param) {
                $key = preg_replace('/^\:/', '', $key);
                if ($param instanceof static) {
                    $value = $param->__toString();
                } elseif (is_numeric($param)) {
                    $value = $param;
                } elseif (is_null($param)) {
                    $value = 'NULL';
                } elseif (is_bool($param)) {
                    $value = $param ? 1 : 0;
                } else {
                    $value = "'" . addslashes($param) . "'";
                }

                $this->query = str_replace(":$key", $value, $this->query);
            }
        }
    }
    public function __toString(): string
    {
        return $this->query ?? $this->sql;
    }
    public function __invoke(): string
    {
        return $this->__toString();
    }
}