<?php

namespace Mysli\Core\Lib;

class Server
{
    protected $config;

    /**
     * Construct SERVER
     * --
     * @param array $config
     *   - url = Default server url
     * @param array $dependencies
     *   - none
     */
    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->config = $config;
    }

    /**
     * Get base URL, with appended URI (if so desired).
     * --
     * @param  string $uri
     * --
     * @return string
     */
    public function url($uri = null)
    {
        $url = \Arr::element('url', $this->config, $this->get_current_url());
        $url = rtrim($url, '/') . '/'; // Always ending slash!
        if ($uri) {
            $url .= ltrim($uri, '/');
        }
        return $url;
    }

    /**
     * Will get current domain
     * --
     * @return string
     */
    public function get_domain()
    {
        return $_SERVER['SERVER_NAME'];
    }

    /**
     * Return current url, if with_query is set to true, it will return full url,
     * query included.
     * --
     * @param  boolean $with_query
     * @param  boolean $trim       Will remove end slash
     * --
     * @return  string
     */
    public function get_current_url($with_query = false, $trim = true)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $url = ($this->is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        } else {
            $url = '';
        }

        if ($with_query) {
            // Make sure we have ending '/'!
            $url = trim($url, '/') . '/';
            $url = $url . ltrim($_SERVER['REQUEST_URI'], '/');
        }

        if ($trim) {
            $url = rtrim($url, '/');
        }

        return $url;
    }

    /**
     * Check if we're on SSL connection.
     * _Borrowed from Wordpress_
     * --
     * @return boolean
     */
    public function is_ssl() {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }
        return false;
    }
}
