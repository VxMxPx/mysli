<?php

namespace mysli\web\request;

__use(__namespace__, '
    mysli/framework/type/{arr,str}
');

class request {

    // Request methods types
    const method_get    = 'GET';
    const method_post   = 'POST';
    const method_put    = 'PUT';
    const method_delete = 'DELETE';

    private static $segments = false;

    /**
     * Get parituclar segment(s) e.g.: page.php/segment0/segment1/segment2
     * @param  string $id      false:   return all segments
     *                         integer: return paticular segment (zero based)
     * @param  mixed  $default
     * @return mixed
     */
    static function segment($id=false, $default=null) {
        if (self::$segments === false) {
            self::$segments = trim(self::path(), '/');
            self::$segments = str::split_trim(self::$segments, '/');
        }
        if ($id === false) {
            return self::$segments;
        } else {
            return arr::get(self::$segments, $id, $default);
        }
    }
    /**
     * Get URI _GET segment.
     * @param  mixed $key     false:  return all keys
     *                        string: return particular key if exists
     *                        array:  return keys specified in array
     * @param  mixed $default Default value(s) when key not found
     * @return mixed
     */
    static function get($key=false, $default=null) {

        if (!$key) {
            return $_GET;
        }

        if (is_array($key)) {
            return arr::get_all($_GET, $key, $default);
        } else {
            return arr::get($_GET, $key, $default);
        }
    }
    /**
     * Get _POST segment.
     * @param  mixed $key     false: return all keys,
     *                        string: return particular key
     *                        array:  return keys specified in array
     * @param  mixed $default Default value(s) when key not found
     * @return mixed
     */
    static function post($key=false, $default=null) {

        if (!$key) {
            return $_POST;
        }

        if (is_array($key)) {
            return arr::get_all($_POST, $key, $default);
        } else {
            return arr::get($_POST, $key, $default);
        }
    }
    /**
     * Name and revision of the information protocol
     * via which the page was requested; i.e. 'HTTP/1.0';
     * @param  string $default
     * @return string
     */
    static function protocol($default='HTTP/1.1') {
        if (isset($_SERVER['SERVER_PROTOCOL'])) {
            return $_SERVER['SERVER_PROTOCOL'];
        } else {
            return $default;
        }
    }
    /**
     * Get IP from which request was made.
     * @return string
     */
    static function ip() {
        return $_SERVER['REMOTE_ADDR'];
    }
    /**
     * Return user's agent.
     * @return  string
     */
    static function agent() {
        return $_SERVER['HTTP_USER_AGENT'];
    }
    /**
     * Check if we're on SSL connection.
     * _Borrowed from Wordpress_
     * @return boolean
     */
    static function is_ssl() {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT'])
        && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }

        return false;
    }
    /**
     * Retrun get current domain
     * @return string
     */
    static function host() {
        return isset($_SERVER['SERVER_NAME'])
            ? $_SERVER['SERVER_NAME']
            : isset($_SERVER['HTTP_HOST'])
                ? $_SERVER['HTTP_HOST']
                : null;
    }
    /**
     * Return client-provided pathname information trailing the actual script
     * filename but preceding the query string, if available.
     * For instance, if the current script was accessed via the URL
     * http://www.example.com/php/path_info.php/some/stuff?foo=bar,
     * then this would return /some/stuff.
     * @return string
     */
    static function path() {
        if (isset($_SERVER['PATH_INFO'])) {
            return $_SERVER['PATH_INFO'];
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $path = explode('?', $_SERVER['REQUEST_URI'])[0];
            $script_name = isset($_SERVER['SCRIPT_NAME'])
                                            ? $_SERVER['SCRIPT_NAME'] : null;
            if (substr($path, 0, strlen($script_name)) === $script_name) {
                $path = substr($path, strlen($script_name));
            }
            return $path;
        }
        return null;
    }
    /**
     * Return current url, if with_query is set to true,
     * it will return full url, query included.
     * @param   boolean $with_query
     * @return  string
     */
    static function url($with_query=false) {

        $url = (self::is_ssl() ? 'https://' : 'http://') . self::host();

        if ($with_query) {
            // Make sure we have ending '/'!
            $url = trim($url, '/') . '/';
            $url = $url . ltrim($_SERVER['REQUEST_URI'], '/');
        }

        return rtrim($url, '/');
    }
    /**
     * Modify current uri query, and return new query.
     * ?k1=val&k2=val2
     * @param  array $segments
     * @return string
     */
    static function modify_query(array $segments) {
        $query = $_GET;
        foreach ($segments as $key => $val) {
            $query[$key] = $val;
        }
        return '?' . http_build_query($query);
    }
    /**
     * Return true if any data was posted, and false if wasn't
     * @param  string $key is particular key set int post
     * @return boolean
     */
    static function has_post($key=false) {
        return $key ? isset($_POST[$key]) : !empty($_POST);
    }
    /**
     * Get request method: request::method_get, request::method_post,
     *                     request::method_put, request::method_delete
     * @return string
     */
    static function method() {
        // Is it put?
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' ||
            self::post('REQUEST_METHOD') === 'PUT') {
            return self::method_put;
        }

        // Is it delete?
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE'
        || self::post('REQUEST_METHOD') === 'DELETE'
        || self::get('request_method') === 'delete') {
            return self::method_delete;
        }

        // Is it post?
        if (self::has_post()) {
            return self::method_post;
        }

        // It must be get.
        return self::method_get;
    }
}
