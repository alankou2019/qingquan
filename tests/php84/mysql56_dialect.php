<?php

if (PHP_VERSION_ID < 80400) {
    fwrite(STDERR, "PHP 8.4 or later is required\n");
    exit(1);
}

if (!extension_loaded('phalcon')) {
    fwrite(STDERR, "Phalcon extension is required\n");
    exit(1);
}

require_once dirname(__DIR__, 2) . '/app/code/Core/Db/Mysql56Dialect.php';

$dialect = new \ScshuxCms\Core\Db\Mysql56Dialect();
$sql = $dialect->describeColumns('scsx_module', 'wecom_kpi_php84_staging');

if (strpos($sql, "'' AS `GenerationExpression`") === false) {
    fwrite(STDERR, "Compatibility generation expression is missing\n");
    exit(1);
}

if (strpos($sql, 'GENERATION_EXPRESSION AS') !== false) {
    fwrite(STDERR, "MySQL 5.7 generation column is still present\n");
    exit(1);
}

fwrite(STDOUT, "PASS: MySQL 5.6 Phalcon dialect compatibility\n");