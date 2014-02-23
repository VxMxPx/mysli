<?php

namespace Mysli;

class Web
{
    protected $pubpath;

    protected $event;
    protected $response;
    protected $output;

    /**
     * Construct Web object.
     * --
     * @param object $config   ~config
     * @param object $event    ~event
     * @param object $response ~response
     * @param object $output   ~output
     */
    public function __construct($config, $event, $response, $output)
    {
        // This is defined in index.php
        // $this->pubpath = MYSLI_PUBPATH;
        $this->pubpath = realpath(datpath($config->get('relative_path')));

        $this->event = $event;
        $this->response = $response;
        $this->output = $output;
    }

    /**
     * Apply headers and set output.
     * --
     * @return null
     */
    public function output(&$output)
    {
        if ($this->response->get_status() === 0) {
            $this->response->status_200_ok();
        }
        $this->response->apply_headers();

        $output  = is_string($output) ? $output : '';
        $output .= $this->output->as_html();
    }

    /**
     * Get absolute public path.
     * --
     * @param  string ... Append to the path.
     * --
     * @return string
     */
    public function path()
    {
        $arguments = func_get_args();
        $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
        return ds($this->pubpath, $arguments);
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
        $url = $this->config->get('url', $this->get_current_url());
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