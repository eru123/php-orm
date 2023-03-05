<?php

namespace eru123\orm;

use Exception;

class Model
{
    /**
     * @var ORM
     */
    protected $orm;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var ?string
     */
    protected $primaryKey;

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
     * Parse Query
     * @param string $query
     * @param array $params
     * @return Raw
     */
    public static function raw(string $sql, array $params = []): Raw
    {
        return new Raw($sql, $params);
    }

    /**
     * Parse Type
     * @param string $type
     * @param int $length
     * @return string|array
     */
    private static function type(string $type, int $length = null, bool $array = false): string|array
    {
        $types = [
            'int' => [
                'type' => 'INT',
                'min' => 1,
                'max' => 11,
            ],
            'tinyint' => [
                'type' => 'TINYINT',
                'min' => 1,
                'max' => 4,
            ],
            'smallint' => [
                'type' => 'SMALLINT',
                'min' => 1,
                'max' => 6,
            ],
            'mediumint' => [
                'type' => 'MEDIUMINT',
                'min' => 1,
                'max' => 9,
            ],
            'bigint' => [
                'type' => 'BIGINT',
                'min' => 1,
                'max' => 20,
            ],
            'float' => [
                'type' => 'FLOAT',
                'min' => 1,
                'max' => 24,
            ],
            'double' => [
                'type' => 'DOUBLE',
                'min' => 1,
                'max' => 53,
            ],
            'decimal' => [
                'type' => 'DECIMAL',
                'min' => 1,
                'max' => 65,
            ],
            'char' => [
                'type' => 'CHAR',
                'min' => 1,
                'max' => 255,
            ],
            'varchar' => [
                'type' => 'VARCHAR',
                'min' => 1,
                'max' => 255,
            ],
            'tinytext' => [
                'type' => 'TINYTEXT',
            ],
            'text' => [
                'type' => 'TEXT',
            ],
            'mediumtext' => [
                'type' => 'MEDIUMTEXT',
            ],
            'longtext' => [
                'type' => 'LONGTEXT',
            ],
            'tinyblob' => [
                'type' => 'TINYBLOB',
            ],
            'blob' => [
                'type' => 'BLOB',
            ],
            'mediumblob' => [
                'type' => 'MEDIUMBLOB',
            ],
            'longblob' => [
                'type' => 'LONGBLOB',
            ],
            'date' => [
                'type' => 'DATE',
            ],
            'datetime' => [
                'type' => 'DATETIME',
            ],
            'timestamp' => [
                'type' => 'TIMESTAMP',
            ],
            'time' => [
                'type' => 'TIME',
            ],
            'year' => [
                'type' => 'YEAR',
            ],
            'enum' => [
                'type' => 'ENUM',
            ],
            'set' => [
                'type' => 'SET',
            ],
            'bool' => [
                'type' => 'TINYINT',
                'length' => 1,
            ],
            'boolean' => [
                'type' => 'TINYINT',
                'length' => 1,
            ],
        ];

        if (!isset($types[$type])) {
            throw new Exception('Invalid type');
        }

        $length = (int) $length;
        if ($length && ($length < $types[$type]['min'] || $length > $types[$type]['max'])) {
            throw new Exception('Invalid length');
        }

        if ($array) {
            return [
                'type' => $types[$type]['type'],
                'length' => $length,
            ];
        }

        return $types[$type]['type'] . ($length ? "({$length})" : '');
    }

    /**
     * Get Create Table Query
     * @return string SQL Query
     */
    public function sqlCreateQuery(): string
    {
        $fields = [];
        foreach ($this->fields as $name => $field) {
            $type = static::type($field['type'], $field['length']);
            $sql = static::raw("`{$name}` {$type}");

            if (isset($field['default'])) {
                $sql .= ' DEFAULT ' . $field['default'];
            }

            if (isset($field['null']) && $field['null'] === false) {
                $sql .= ' NOT NULL';
            }

            if ($this->primaryKey === $name) {
                $sql .= ' AUTO_INCREMENT';
            }

            $fields[] = $sql;
        }

        if ($this->primaryKey) {
            $fields[] = static::raw('PRIMARY KEY (`' . $this->primaryKey . '`)');
        }

        foreach ($this->indexes as $index) {
            $index_fields = array_map(function ($index) {
                $field = strtoupper($index['field']);
                return "`{$field}`";
            }, $this->indexes);
            $index_fields = implode(', ', $index_fields);
            $fields[] = static::raw("{$index['type']} KEY `{$index['name']}` (`{$index_fields}`)");
        }

        $fields = implode(', ', $fields);
        return static::raw("CREATE TABLE IF NOT EXISTS {$this->table} ({$fields})");
    }

    /**
     * Get Drop Table Query
     * @return string SQL Query
     */
    public function sqlDropQuery(): string
    {
        return static::raw("DROP TABLE IF EXISTS {$this->table}");
    }

    /**
     * Get Describe Table Query
     * @return string SQL Query
     */
    public function sqlDescribeQuery(): string
    {
        return static::raw("DESCRIBE {$this->table}");
    }

    /**
     * Analyze Describe Table Result and Create Alter Table Query
     * @param array $result
     * @return string SQL Query
     */
    public function sqlAlterQuery(array $result): string
    {
        $intended_fields = $this->fields;
        $intended_indexes = $this->indexes;
        $intended_primary_key = $this->primaryKey;

        $parse_result = [];

        foreach ($result as $row) {
            $parse_result[$row['Field']] = [
                'field' => $row['Field'],
                'null' => $row['Null'] === 'YES',
                'default' => $row['Default'],
                'extra' => $row['Extra'],
            ];

            if (preg_match('/^(\w+)\((\d+)\)$/', $row['Type'], $matches)) {
                $parse_result[$row['Field']]['type'] = $matches[1];
                $parse_result[$row['Field']]['length'] = $matches[2];
            } else {
                $parse_result[$row['Field']]['length'] = static::type($row['Type'], null, true)['length'];
            }

            if ($row['Key'] === 'PRI') {
                $parse_result[$row['Field']]['primary_key'] = true;
            }

            if ($row['Key'] === 'MUL') {
                $parse_result[$row['Field']]['index'] = true;
            }

            if ($row['Key'] === 'UNI') {
                $parse_result[$row['Field']]['unique'] = true;
            }

            if ($row['Extra'] === 'auto_increment') {
                $parse_result[$row['Field']]['auto_increment'] = true;
            }
        }

        $alter_fields = [];
        $alter_indexes = [];
        $alter_primary_key = null;

        foreach ($intended_fields as $name => $field) {
            $type = static::type($field['type'], $field['length']);
            $sql = static::raw("`{$name}` {$type}");

            if (isset($field['default'])) {
                $sql .= ' DEFAULT ' . $field['default'];
            }

            if (isset($field['null']) && $field['null'] === false) {
                $sql .= ' NOT NULL';
            }

            if ($intended_primary_key === $name) {
                $sql .= ' AUTO_INCREMENT';
            }

            if (isset($parse_result[$name])) {
                if ($parse_result[$name]['type'] !== $type) {
                    $alter_fields[] = static::raw("CHANGE `{$name}` {$sql}");
                }
            } else {
                $alter_fields[] = static::raw("ADD {$sql}");
            }
        }

        foreach ($parse_result as $name => $field) {
            if (!isset($intended_fields[$name])) {
                $alter_fields[] = static::raw("DROP `{$name}`");
            }
        }

        foreach ($intended_indexes as $index) {
            $index_fields = array_map(function ($index) {
                $field = strtoupper($index['field']);
                return "`{$field}`";
            }, $this->indexes);
            $index_fields = implode(', ', $index_fields);
            $sql = static::raw("{$index['type']} KEY `{$index['name']}` (`{$index_fields}`)");

            if (isset($parse_result[$name])) {
                if ($parse_result[$name]['type'] !== $type) {
                    $alter_indexes[] = static::raw("CHANGE `{$name}` {$sql}");
                }
            } else {
                $alter_indexes[] = static::raw("ADD {$sql}");
            }
        }

        foreach ($parse_result as $name => $field) {
            if (!isset($intended_fields[$name])) {
                $alter_indexes[] = static::raw("DROP `{$name}`");
            }
        }

        if ($intended_primary_key !== $parse_result[$name]['primary_key']) {
            $alter_primary_key = $intended_primary_key;
        }

        $alter_fields = implode(', ', $alter_fields);
        $alter_indexes = implode(', ', $alter_indexes);
        $alter_primary_key = $alter_primary_key ? static::raw("ADD PRIMARY KEY (`{$alter_primary_key}`)") : null;

        $alter = [];
        if ($alter_fields) {
            $alter[] = $alter_fields;
        }

        if ($alter_indexes) {
            $alter[] = $alter_indexes;
        }

        if ($alter_primary_key) {
            $alter[] = $alter_primary_key;
        }

        $alter = implode(', ', $alter);
        return static::raw("ALTER TABLE {$this->table} {$alter}");
    }
}