<?php

namespace mysli\web\request;

__use(__namespace__,
    'mysli/framework/type/arr'
);

class request {

    // Request methods types
    const method_get    = 'GET';
    const method_post   = 'POST';
    const method_put    = 'PUT';
    const method_delete = 'DELETE';

    private static $segments = false;

    /**
     * Get parituclar segment(s) e.g.: page.php/segment0/segment1/segment2
     * @param  string $id      false:   return fall segments
     *                         integer: return paticular segment (zero based)
     * @param  mixed  $default
     * @return mixed
     */
    static function segement($id=false, $default=null) {
        if (self::$segments === false) {
            self::$segments = trim(self::get_path_info(), '/');
            self::$segments = str::split_trim($segments, '/');
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
     * Return true if any data was posted, and false if wasn't
     * @param  string $key is particular key set int post
     * @return boolean
     */
    static function has_post($key=false) {
        return $key ? isset($_POST[$key]) : !empty($_POST);
    }
    /**
     * Get server's path info.
     * @return string
     */
    static function get_path_info() {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
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
            return arr::get($key, $_POST, $default);
        }
    }
    /**
     * Get request method: request::method_get, request::method_post,
     *                     request::method_put, request::method_delete
     * @return string
     */
    static function get_method() {
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
