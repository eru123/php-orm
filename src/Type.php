<?php

namespace eru123\orm;

class Type {
    const INT = 'INT';
    const INT_MIN = 1;
    const INT_MAX = 11;

    const VARCHAR = 'VARCHAR';
    const VARCHAR_MIN = 1;
    const VARCHAR_MAX = 255;

    const TEXT = 'TEXT';
    const DATETIME = 'DATETIME';
    const TIMESTAMP = 'TIMESTAMP';
    const DATE = 'DATE';
    const TIME = 'TIME';
    const YEAR = 'YEAR';
    const FLOAT = 'FLOAT';
    const DOUBLE = 'DOUBLE';
    const DECIMAL = 'DECIMAL';
    const BIT = 'BIT';
    const BOOL = 'BOOL';
    const ENUM = 'ENUM';
    const SET = 'SET';
    const BLOB = 'BLOB';
    const TINYBLOB = 'TINYBLOB';
    const MEDIUMBLOB = 'MEDIUMBLOB';
    const LONGBLOB = 'LONGBLOB';
    const BINARY = 'BINARY';
    const VARBINARY = 'VARBINARY';
    const TINYINT = 'TINYINT';
    const SMALLINT = 'SMALLINT';
    const MEDIUMINT = 'MEDIUMINT';
    const BIGINT = 'BIGINT';
    const TINYTEXT = 'TINYTEXT';
    const MEDIUMTEXT = 'MEDIUMTEXT';
    const LONGTEXT = 'LONGTEXT';
    const CHAR = 'CHAR';
    const GEOMETRY = 'GEOMETRY';
    const POINT = 'POINT';
    const LINESTRING = 'LINESTRING';
    const POLYGON = 'POLYGON';
    const MULTIPOINT = 'MULTIPOINT';
    const MULTILINESTRING = 'MULTILINESTRING';
    const MULTIPOLYGON = 'MULTIPOLYGON';
    const GEOMETRYCOLLECTION = 'GEOMETRYCOLLECTION';
    const JSON = 'JSON';
}