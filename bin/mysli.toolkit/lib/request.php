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
    private static $segments = [];

    /**
     * Set segments.
     * --
     * @param string $path
     *        Initial path, is not provided `self::path()` will be used.
     */
    static function __init($path=null)
    {
        $path = $path ?: static::path();
        $segments = trim($path, '/');
        static::$segments = str::split_trim($segments, '/');
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
     *        false  return all keys
     *        string return particular key
     *        array  return keys specified in array
     *
     * @param mixed $default
     *        Default value(s) when key not found.
     * --
     * @return mixed
     *         String or array, deepening on key.
     */
    static function post($key=false, $default=null)
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
        if (isset($_SERVER['SERVER_PROTOCOL']))
        {
            return $_SERVER['SERVER_PROTOCOL'];
        }
        else
        {
            return $default;
        }
    }

    /**
     * Get IP from which request was made.
     * --
     * @return string
     */
    static function ip()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Get user's agent.
     * --
     * @return  string
     */
    static function agent()
    {
        return $_SERVER['HTTP_USER_AGENT'];
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
        if (isset($_SERVER['HTTPS']))
        {
            if ('on' == strtolower($_SERVER['HTTPS']))
            {
                return true;
            }

            if ('1' == $_SERVER['HTTPS'])
            {
                return true;
            }
        }
        elseif (isset($_SERVER['SERVER_PORT']) &&
            '443' == $_SERVER['SERVER_PORT'])
        {
            return true;
        }

        return false;
    }

    /**
     * Return current domain.
     * --
     * @return string
     */
    static function host()
    {
        if (isset($_SERVER['SERVER_NAME']))
        {
            return $_SERVER['SERVER_NAME'];
        }
        elseif (isset($_SERVER['HTTP_HOST']))
        {
            return $_SERVER['HTTP_HOST'];
        }
        else
        {
            return null;
        }
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

            $script_name = isset($_SERVER['SCRIPT_NAME'])
                ? $_SERVER['SCRIPT_NAME']
                : null;

            if (substr($path, 0, strlen($script_name)) === $script_name)
            {
                $path = substr($path, strlen($script_name));
            }

            return $path;
        }

        return null;
    }

    /**
     * Return current URL.
     * If with_query is set to true, it will return full URL, query included.
     * --
     * @param boolean $with_query
     * --
     * @return string
     */
    static function url($with_query=false)
    {
        $url = (static::is_ssl() ? 'https://' : 'http://') . static::host();

        if ($with_query)
        {
            // Make sure there's ending '/'
            $url = trim($url, '/') . '/';
            $url = $url . ltrim($_SERVER['REQUEST_URI'], '/');
        }

        return rtrim($url, '/');
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
        if (static::has_post())
        {
            return self::method_post;
        }

        // It must be get.
        return self::method_get;
    }
}
