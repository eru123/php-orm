<?php

namespace eru123\orm;

trait Helper
{
    private static function to_many_data(array $data)
    {
        $ndata = [];
        if (array_keys($data) !== range(0, count($data) - 1)) {
            $ndata[] = $data;
        } else {
            $ndata = $data;
        }

        return $ndata;
    }

    /**
     * Parse Query
     * @param string $query
     * @param array $params
     * @return Raw
     */
    public static function raw(string $sql, array $params = []): Raw
    {
        return new Raw($sql, $params);
    }
}