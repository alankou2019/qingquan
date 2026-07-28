<?php

namespace ScshuxCms\Core\Cache;

class CacheFactory
{
    public static function create($config)
    {
        $cachePath = WEBROOT . '/var/cache/';
        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0777, true);
        }

        $lifetime = 3600;
        if (isset($config->cache->lifetime)) {
            $lifetime = intval($config->cache->lifetime);
        } elseif (isset($config->cache->options->lifetime)) {
            $lifetime = intval($config->cache->options->lifetime);
        }

        $serializerFactory = new \Phalcon\Storage\SerializerFactory();
        $adapter = new \Phalcon\Cache\Adapter\Stream(
            $serializerFactory,
            array(
                'defaultSerializer' => 'Php',
                'lifetime' => $lifetime,
                'storageDir' => $cachePath,
            )
        );
        $cache = new \Phalcon\Cache\Cache($adapter);

        return new LegacyCache($cache);
    }
}
