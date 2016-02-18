<?php

namespace mysli\toolkit; class request
{
    const __use = '.type.{arr, str}';

    // Request methods types
    const method_get    = 'GET';
    const method_post   = 'POST';
    const method_put    = 'PUT';
    const method_delete = 'DELETE';

    /**
     * All segments of current request.
     * --
     * @var array
     */
    protected static $segments = [];

    /**
     * Request time.
     * --
     * @var integer
     */
    protected static $time;

    /**
     * Set segments.
     * --
     * @param string $path
     *        Initial path, is not provided `self::path()` will be used.
     */
    static function __init($path=null)
    {
        static::$time = static::server('request_time', time());

        $path = $path ?: static::path();
        $segments = trim($path, '/');
        static::$segments = str::split_trim($segments, '/');

        \log::debug(
            "Request URL: `".static::url(true)."`, segments: `{$segments}` from: `".
            static::ip()."`. Method: `".static::method()."`.",
            __CLASS__);
    }

    /**
     * Get value from $_SERVER array.
     * --
     * @param string $key
     * @param mixed $default
     * --
     * @return mixed
     */
    static function server($key, $default=null)
    {
        $key = strtoupper($key);
        return array_key_exists($key, $_SERVER)
            ? $_SERVER[$key]
            : $default;
    }

    /**
     * Return request time.
     * --
     * @return integer
     */
    static function time()
    {
        return static::$time;
    }

    /**
     * Return server port.
     * --
     * @return integer
     */
    static function port()
    {
        return (int) static::server('server_port', 0);
    }

    /**
     * Get parituclar segment(s) e.g.: `page.php/segment0/segment1/segment2`
     * --
     * @param integer $id
     *        null    return all segments
     *        integer return paticular segment (zero based)
     *
     * @param  mixed  $default
     * --
     * @return mixed
     */
    static function segment($id=null, $default=null)
    {
        if ($id === null)
        {
            return static::$segments;
        }
        else
        {
            return arr::get(static::$segments, $id, $default);
        }
    }

    /**
     * Get URI _GET values.
     * --
     * @param mixed $key
     *        null   return all keys
     *        string return particular key if exists
     *        array  return keys specified in array
     *
     * @param mixed $default
     *        Default value(s) when key not found.
     * --
     * @return mixed
     *         String or array, depending on key.
     */
    static function get($key=null, $default=null)
    {
        if (!$key)
        {
            return $_GET;
        }

        if (is_array($key))
        {
            return arr::get_all($_GET, $key, $default);
        }
        else
        {
            return arr::get($_GET, $key, $default);
        }
    }

    /**
     * Get _POST values.
     * --
     * @param mixed $key
     *        null   return all keys
     *        string return particular key
     *        array  return keys specified in array
     *
     * @param mixed $default
     *        Default value(s) when key not found.
     * --
     * @return mixed
     *         String or array, deepening on key.
     */
    static function post($key=null, $default=null)
    {
        if (!$key)
        {
            return $_POST;
        }

        if (is_array($key))
        {
            return arr::get_all($_POST, $key, $default);
        }
        else
        {
            return arr::get($_POST, $key, $default);
        }
    }

    /**
     * Name and revision of the information protocol
     * via which the page was requested; i.e. 'HTTP/1.0';
     * --
     * @param string $default
     * --
     * @return string
     */
    static function protocol($default='HTTP/1.1')
    {
        return static::server('server_protocol', $default);
    }

    /**
     * Get IP from which request was made.
     * --
     * @return string
     */
    static function ip()
    {
        return static::server('remote_addr');
    }

    /**
     * Get user's agent.
     * --
     * @return  string
     */
    static function agent()
    {
        return static::server('http_user_agent');
    }

    /**
     * Check if it's SSL connection.
     * --
     * @author Wordpress
     * --
     * @return boolean
     */
    static function is_ssl()
    {
        return in_array(strtolower(static::server('https')), ['on', '1'])
            || static::server('server_port') === '443';
    }

    /**
     * Return current domain.
     * --
     * @return string
     */
    static function host()
    {
        return static::server('server_name');
    }

    /**
     * Return client-provided pathname information trailing the actual script
     * filename but preceding the query string, if available.
     * For instance, if the current script was accessed via the URL
     * http://www.example.com/php/path_info.php/some/stuff?foo=bar,
     * then this would return /some/stuff.
     * --
     * @return string
     */
    static function path()
    {
        if (isset($_SERVER['PATH_INFO']))
        {
            return $_SERVER['PATH_INFO'];
        }

         if (isset($_SERVER['REQUEST_URI']))
         {
             $path = explode('?', $_SERVER['REQUEST_URI'])[0];
             $path = substr($path, strlen(static::server('script_name')));
             return $path;
         }

         if (isset($_SERVER['PHP_SELF']))
         {
            $path = $_SERVER['PHP_SELF'];
            $path = substr($path, strlen(static::server('script_name')));
            return $path;
         }

        return '/'.implode('/', static::segment());
    }

    /**
     * Return current URL.
     * If with_query is set to true, it will return full URL,
     * with path and query included.
     * --
     * @param boolean $with_query
     * @param boolean $ns_port    Add non-standard port (!== 80, 443)
     * @param string  $sub_domain Prepend sub domain
     * --
     * @return string
     */
    static function url($with_query=false, $ns_port=false, $sub_domain=null)
    {
        $url =
            (static::is_ssl() ? 'https://' : 'http://') .
            ($sub_domain ? "{$sub_domain}." : '') .
            static::host();

        if ($ns_port && !in_array(static::port(), [ 80, 443 ]))
        {
            $url .= ':'.static::port();
        }

        if ($with_query)
        {
            // Make sure there's ending '/'
            $url = trim($url, '/') . '/';
            $url = $url . ltrim(static::server('request_uri'), '/');
        }

        return rtrim($url, '/');
    }

    /**
     * Return current URI (path+query)
     * --
     * @return string
     */
    static function uri()
    {
        return static::server('request_uri');
    }

    /**
     * Modify current URI query, and return new query, example: ?k1=val&k2=val2
     * --
     * @param array $segments
     * --
     * @return string
     */
    static function modify_query(array $segments)
    {
        $query = $_GET;

        foreach ($segments as $key => $val)
        {
            $query[$key] = $val;
        }

        return '?' . http_build_query($query);
    }

    /**
     * Return true if any data was posted.
     * --
     * @param string $key
     *        Is particular key set in post.
     * --
     * @return boolean
     */
    static function has_post($key=null)
    {
        return $key
            ? isset($_POST[$key])
            : !empty($_POST);
    }

    /**
     * Get request method:
     *     request::method_get, request::method_post,
     *     request::method_put, request::method_delete
     * --
     * @param boolean $can_fake
     *        Weather fake method is accepted.
     *        In case of PUT, if data is posted with key REQUEST_METHOD = PUT,
     *        that would mean it's PUT request.
     *        In case of delete, if either GET or POST key exists, with value
     *        REQUEST_METHOD = DELETE, that would make it DELETE.
     * --
     * @return string
     */
    static function method($can_fake=false)
    {
        // No request method
        if (!isset($_SERVER['REQUEST_METHOD']))
        {
            return null;
        }

        // Is it put?
        if ($_SERVER['REQUEST_METHOD'] === 'PUT')
        {
            return self::method_put;
        }

        // Is it delete?
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE')
        {
            return self::method_delete;
        }

        // Check for fakes now
        if ($can_fake)
        {
            if (static::has_post('REQUEST_METHOD'))
            {
                if (strtolower(static::post('REQUEST_METHOD')) === 'delete')
                {
                    return self::method_delete;
                }

                if (strtolower(static::post('REQUEST_METHOD')) === 'put')
                {
                    return self::method_put;
                }
            }

            if (strtolower(static::get('REQUEST_METHOD')) === 'delete')
            {
                return self::method_delete;
            }
        }

        // Is it post?
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            return self::method_post;
        }

        // It must be get.
        return self::method_get;
    }
}
