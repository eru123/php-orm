<?php

namespace eru123\orm\Table;

use eru123\orm\State;
use Exception;

trait Management
{
    use Base;

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
            $type = static::type($field['type'], @$field['length']);
            $sql = static::raw("`{$name}` {$type}");

            if (isset($field['default'])) {
                $sql .= ' DEFAULT ' . $field['default'];
            }

            if (isset($field['null']) && $field['null'] === false) {
                $sql .= ' NOT NULL';
            }

            if ($this->primary_key === $name) {
                $sql .= ' AUTO_INCREMENT';
            }

            $fields[] = $sql;
        }

        if ($this->primary_key) {
            $fields[] = static::raw('PRIMARY KEY (`' . $this->primary_key . '`)');
        }

        $missing_index_cols = [];
        foreach ($this->indexes as $key => $index) {
            $type = strtoupper($index['type']);

            $cols = [];
            foreach ($index['columns'] as $col) {
                if (!isset($this->fields[$col])) {
                    $missing_index_cols[$key][] = $col;
                    continue;
                }

                $cols[] = "`{$col}`";
            }
            $cols_sql = implode(', ', $cols);
            $fields[] = static::raw("{$type} KEY `{$key}` ({$cols_sql})");
        }

        if (count($missing_index_cols) > 0) {
            $missing_index_cols_msg =[];
            foreach ($missing_index_cols as $key => $cols) {
                foreach ($cols as $i => $col) {
                    $cols[$i] = "`{$col}`";
                }
                $missing_index_cols_msg[] = "`{$key}` (" . implode(', ', $cols) . ")";
            }
            throw new Exception('Missing columns for indexes: ' . implode(', ', $missing_index_cols_msg));
        }

        $fields = implode(', ', $fields);
        return static::raw("CREATE TABLE IF NOT EXISTS {$this->table} ({$fields})");
    }

    /**
     * Create Alter Table Query
     * @param array $alter_table
     * @return string SQL Query
     */
    public function sqlAlterQuery(array $alter_table): string
    {
        $fields = [];
        foreach ($alter_table as $name => $field) {
            $type = static::type($field['type'], @$field['length']);
            $sql = static::raw("ADD COLUMN `{$name}` {$type}");

            if (isset($field['default'])) {
                $sql .= ' DEFAULT ' . $field['default'];
            }

            if (isset($field['null']) && $field['null'] === false) {
                $sql .= ' NOT NULL';
            }

            $fields[] = $sql;
        }

        $fields = implode(', ', $fields);
        return static::raw("ALTER TABLE {$this->table} {$fields}");
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
     * Migrate Table
     * @return static
     */
    public function migrate()
    {
        $create = false;
        $alter = true;

        try {
            $sql = $this->sqlDescribeQuery();
            $description_raw = $this->orm()->exec($sql)->stmt->fetchAll();
            $create = false;
        } catch (Exception $e) {
            $alter = false;
            if ($e->getCode() === State::NOTABLE) {
                $create = true;
            } else {
                new Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        if ($create) {
            $this->orm()->debug_info(' --- creating database', 'yellow');
            $sql = $this->sqlCreateQuery();
            $this->orm()->exec($sql);

            try {
                $sql = $this->sqlDescribeQuery();
                $this->orm()->exec($sql)->stmt->fetchAll();
            } catch (Exception $e) {
                if ($e->getCode() === State::NOTABLE) {
                    new Exception("Table {$this->table} was not created", 500, $e);
                } else {
                    new Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        if ($alter && !empty($description_raw)) {
            $this->orm()->debug_info(' --- altering database', 'yellow');

            $desciption = [];
            $missing_fields = [];
            foreach ($description_raw as $row) {
                $field = $row['Field'];
                $desciption[$field] = [
                    'type' => $row['Type'],
                    'null' => $row['Null'] === 'YES',
                    'default' => $row['Default'] ?? 'NULL',
                ];

                if (!isset($this->fields[$field])) {
                    $missing_fields[] = $field;
                }
            }

            if (!empty($missing_fields)) {
                $this->orm()->debug_info(' --- [WARNING] missing fields: ' . implode(', ', $missing_fields), 'red');
            }

            $alter_table = [];
            foreach ($this->fields as $field => $info) {
                if (!isset($desciption[$field])) {
                    $alter_table[$field] = $info;
                }
            }

            if (empty($alter_table)) {
                $this->orm()->debug_info(' --- no changes', 'yellow');
                return $this;
            }

            $sql = $this->sqlAlterQuery($alter_table);
            $this->orm()->exec($sql);

            try {
                $sql = $this->sqlDescribeQuery();
                $description_raw = $this->orm()->exec($sql)->stmt->fetchAll();
            } catch (Exception $e) {
                if ($e->getCode() === State::NOTABLE) {
                    new Exception("Table {$this->table} was not created", 500, $e);
                } else {
                    new Exception($e->getMessage(), $e->getCode(), $e);
                }
            }
        }

        return $this;
    }

    /**
     * Drop Table
     * @return static
     */
    public function drop()
    {
        try {
            $sql = $this->sqlDropQuery();
            $this->orm()->exec($sql);

            $sql = $this->sqlDescribeQuery();
            $this->orm()->exec($sql)->stmt->fetchAll();
        } catch (Exception $e) {
            if ($e->getCode() !== State::NOTABLE) {
                new Exception($e->getMessage(), $e->getCode(), $e);
            }
        }

        return $this;
    }
}