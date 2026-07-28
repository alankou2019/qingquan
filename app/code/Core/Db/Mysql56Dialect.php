<?php

namespace ScshuxCms\Core\Db;

/**
 * Keeps Phalcon 5 column introspection compatible with MySQL 5.6.
 *
 * MySQL 5.6 does not expose INFORMATION_SCHEMA.COLUMNS.GENERATION_EXPRESSION.
 * This legacy application has no generated columns, so the compatibility
 * dialect returns an empty expression while preserving Phalcon's result shape.
 */
class Mysql56Dialect extends \Phalcon\Db\Dialect\Mysql
{
    public function describeColumns(string $table, ?string $schema = null): string
    {
        $schemaClause = $schema ? "'" . $schema . "'" : 'DATABASE()';

        return "SELECT COLUMN_NAME AS `Field`, COLUMN_TYPE AS `Type`, "
            . "COLLATION_NAME AS `Collation`, IS_NULLABLE AS `Null`, "
            . "COLUMN_KEY AS `Key`, COLUMN_DEFAULT AS `Default`, "
            . "EXTRA AS `Extra`, PRIVILEGES AS `Privileges`, "
            . "COLUMN_COMMENT AS `Comment`, "
            . "'' AS `GenerationExpression` "
            . "FROM `INFORMATION_SCHEMA`.`COLUMNS` "
            . "WHERE `TABLE_SCHEMA` = " . $schemaClause . " "
            . "AND `TABLE_NAME` = '" . $table . "' "
            . "ORDER BY `ORDINAL_POSITION`";
    }
}
