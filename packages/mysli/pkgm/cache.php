<?php

namespace Mysli\Pkgm;

class Cache
{
    private $objects = [];

    /**
     * Add new object to the cache.
     * --
     * @param string $pkg
     * @param object $object
     * --
     * @return null
     */
    public function add($pkg, $object)
    {
        $this->objects[$pkg] = $object;
    }

    /**
     * Remove object from cache.
     * --
     * @param  string $pkg
     * --
     * @return null
     */
    public function remove($pkg)
    {
        if (isset($this->objects[$pkg])) {
            unset($this->objects[$pkg]);
        }
    }

    /**
     * Check if object exists in cache.
     * --
     * @param  string $pkg
     * --
     * @return boolean
     */
    public function has($pkg)
    {
        return isset($this->objects[$pkg]);
    }

    /**
     * Get object from cache.
     * --
     * @param  string $pkg
     * --
     * @return object | null if package not in cache.
     */
    public function get($pkg)
    {
        return $this->has($pkg) ? $this->objects[$pkg] : null;
    }
}
