<?php

namespace Mysli;

class Cookie
{
    protected $core;
    protected $config;

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->core = $dependencies['core'];
        $this->config = $config;

        $this->config['domain'] = $this->config['domain'] ?: $_SERVER['SERVER_NAME'];
    }

    /**
     * Create cookie
     * --
     * @param  string $name
     * @param  string $value
     * @param  string $expire  Use false for default expire time (set in
     *                         configuration), or enter value (actual value so
     *                         must be time() + seconds)
     * --
     * @return boolean
     */
    public function create($name, $value, $expire = false)
    {
        if ($expire === false) {
            $expire = time() + $this->config['timeout'];
        }

        $domain = $this->config['domain'];
        $prefix = $this->config['prefix'];

        $this->core->log->info(
            "Cookie will be set, as: `{$prefix}{$name}`, with value: `{$value}`, " .
            "set to expire on: `{$expire}`, to domain: `{$domain}`.",
            __FILE__, __LINE__
        );

        return setcookie($prefix . $name, $value, $expire, '/', $domain);
    }

    /**
     * Fetch an item from the COOKIE array
     * --
     * @param  string $key
     * --
     * @return mixed  String - cookie value / False if no cookie found.
     */
    public function read($key)
    {
        $key_prefix = $this->config['prefix'] . $key;

        if (isset($_COOKIE[$key_prefix])) {
            $return = $_COOKIE[$key_prefix];
        } elseif (isset($_COOKIE[$key])) {
            $return = $_COOKIE[$key];
        } else {
            return false;
        }

        return htmlspecialchars($return);
    }

    /**
     * Remove cookie
     * --
     * @param  string $name
     * --
     * @return boolean
     */
    public function remove($name)
    {
        $domain = $this->config['domain'];
        $prefix = $this->config['prefix'];

        $this->core->log->info(
            "Cookie will be unset: `{$prefix}{$name}`.",
            __FILE__, __LINE__
        );

        return setcookie($prefix . $name, '', time() - 3600, '/', $domain);
    }
}
