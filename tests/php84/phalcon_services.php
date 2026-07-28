<?php

if (PHP_VERSION_ID < 80400) {
    fwrite(STDERR, "FAIL: PHP 8.4+ is required\n");
    exit(1);
}
if (!extension_loaded('phalcon')) {
    fwrite(STDERR, "FAIL: Phalcon extension is not loaded\n");
    exit(1);
}

$temporaryRoot = sys_get_temp_dir() . '/wecom-php84-smoke-' . getmypid();
if (!mkdir($temporaryRoot, 0700, true) && !is_dir($temporaryRoot)) {
    fwrite(STDERR, "FAIL: cannot create temporary root\n");
    exit(1);
}
define('WEBROOT', $temporaryRoot);

require_once dirname(__DIR__, 2) . '/app/code/Core/Cache/LegacyCache.php';
require_once dirname(__DIR__, 2) . '/app/code/Core/Cache/CacheFactory.php';
require_once dirname(__DIR__, 2) . '/app/code/Core/Session/SessionFactory.php';

$configClass = class_exists('\Phalcon\Config\Config')
    ? '\Phalcon\Config\Config'
    : '\Phalcon\Config';
$config = new $configClass(array(
    'cache' => array(
        'lifetime' => 60,
        'options' => array('lifetime' => 60),
    ),
    'session' => array(
        'adapter' => 'Files',
        'options' => array(
            'name' => 'WECOM_PHP84_SMOKE',
            'uniqueId' => 'smoke-',
        ),
    ),
));

$cache = \ScshuxCms\Core\Cache\CacheFactory::create($config);
if (!$cache->save('smoke_key', array('status' => 'ok'), 60)) {
    fwrite(STDERR, "FAIL: cache save failed\n");
    exit(1);
}
$cached = $cache->get('smoke_key');
if (!is_array($cached) || $cached['status'] !== 'ok') {
    fwrite(STDERR, "FAIL: cache read mismatch\n");
    exit(1);
}
if (!$cache->flush()) {
    fwrite(STDERR, "FAIL: cache flush failed\n");
    exit(1);
}

$session = \ScshuxCms\Core\Session\SessionFactory::create($config);
$session->set('smoke_key', 'ok');
if ($session->get('smoke_key') !== 'ok') {
    fwrite(STDERR, "FAIL: session read mismatch\n");
    exit(1);
}
$session->remove('smoke_key');
$session->destroy();

fwrite(STDOUT, "PASS: Phalcon cache and session services\n");
