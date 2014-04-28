<?php

namespace Mysli\Cookie;

class Cookie
{
    protected $config;

    /**
     * Construct Cookie
     * --
     * @param object $config ~config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Create cookie
     * --
     * @param  string $name
     * @param  string $value
     * @param  string $path    The path on the server in which the cookie will be available on.
     *                         If set to '/', the cookie will be available within the entire domain.
     *                         If set to '/foo/', the cookie will only be available within the /foo/ directory
     *                         and all sub-directories such as /foo/bar/ of domain.
     * @param  string $expire  Use false for default expire time (set in
     *                         configuration), or enter value (actual value so
     *                         must be time() + seconds)
     * --
     * @return boolean
     */
    public function create($name, $value, $path = '/', $expire = false)
    {
        if ($expire === false) {
            $expire = time() + $this->config->get('timeout');
        }

        $domain = $this->config->get('domain', $_SERVER['SERVER_NAME']);
        $prefix = $this->config->get('prefix');

        return setcookie($prefix . $name, $value, $expire, $path, $domain);
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
        $key_prefix = $this->config->get('prefix') . $key;

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
     * @param  string $path    The path on the server in which the cookie will be available on.
     *                         If set to '/', the cookie will be available within the entire domain.
     *                         If set to '/foo/', the cookie will only be available within the /foo/ directory
     *                         and all sub-directories such as /foo/bar/ of domain.
     * --
     * @return boolean
     */
    public function remove($name, $path = '/')
    {
        $domain = $this->config->get('domain', $_SERVER['SERVER_NAME']);
        $prefix = $this->config->get('prefix');

        return setcookie($prefix . $name, '', time() - 3600, $path, $domain);
    }
}
