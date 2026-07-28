<?php

namespace ScshuxCms\Core\Cache;

/**
 * Keeps the legacy get/save/flush API while using the Phalcon 5 cache.
 */
class LegacyCache
{
    private $cache;

    public function __construct($cache)
    {
        $this->cache = $cache;
    }

    public function get($key)
    {
        return $this->cache->get($key);
    }

    public function save($key, $value, $lifetime = null)
    {
        return $this->cache->set($key, $value, $lifetime);
    }

    public function flush()
    {
        return $this->cache->clear();
    }
}
