# Requirements
 - PHP ^7.4
 - PDO MySQL
 - MySQL ^5.7

# php-orm
Simple yet Powerful ORM for PHP

# Features
 - Forward-only automatic migrations
 - Use Table Model as Migration
 - Extendible Model using traits
 - Supports both ORM and Eloquent style
 - Provides RAW SQL query builder
 - Can connect to multiple databases
 - Can switch database connection at runtime
 - No dependencies
 - No configuration
 - No relationships

# Constraints
 - MySQL only
 - Primary keys and indexes can be setup only at initial migration
 - No primary keys, auto increment, indexes after initial migration
 - Only adding new columns is supported for altering table
