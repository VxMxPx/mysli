<?php

namespace Mysli\Request;

class Request
{
    use \Mysli\Core\Pkg\Singleton;

    // Request methods types
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';

    // List of all url segments
    private $segments = [];

    // Get segments
    private $get      = [];

    public function __construct()
    {
        // Set get segments
        $this->get = $_GET;

        // Set list of segments
        $segments = trim($this->get_path_info(), '/');
        $segments = \Core\Str::explode_trim('/', $segments);

        // Register segments containing equal sign, to get
        foreach ($segments as $segment) {
            if (strpos($segment, '=') !== false) {
                $segment_get = \Core\Str::explode_trim('=', $segment, 2);
                $this->get[$segment_get[0]] = $segment_get[1];
                continue;
            }
            $this->segments[] = $segment;
        }
    }

    /**
     * Get server's path info.
     * --
     * @return string
     */
    public function get_path_info()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
    }

    /**
     * Return particular uri segment if set, otherwise return default value.
     * --
     * @param  integer  $number
     * @param  mixed    $defult
     * --
     * @return mixed
     */
    public function segment($number, $defult = false)
    {
        return isset($this->segments[$number])
                ? $this->segments[$number]
                : $default;
    }

    /**
     * Return list all segments currently set.
     * --
     * @return array
     */
    public function segments()
    {
        return $this->segments;
    }

    /**
     * Set entirely new list of segments.
     * --
     * @param array $list
     * --
     * return null
     */
    public function set_segments($list)
    {
        $this->segments = $list;
    }

    /**
     * Get URI _GET segment.
     * @param  mixed   $key     Following options are available:
     *                             false:  return all keys
     *                             string: return particular key if exists
     *                             array:  return keys specified in array
     * @param  mixed   $default Default value(s) when key not found
     * --
     * @return mixed
     */
    public function get($key = false, $default = false)
    {
        if (!$key) { return $this->get; }

        if (is_array($key)) {
            return \Core\Arr::elements($key, $this->get, $default);
        }
        else {
            return \Core\Arr::element($key, $this->get, $default);
        }
    }

    /**
     * Get _POST segment.
     * @param  mixed   $key     Following options are available:
     *                             false:  return all keys
     *                             string: return particular key if exists
     *                             array:  return keys specified in array
     * @param  mixed   $default Default value(s) when key not found
     * --
     * @return mixed
     */
    public function post($key = false, $default = false)
    {
        if (!$key) { return $_POST; }

        if (is_array($key)) {
            return \Core\Arr::elements($key, $_POST, $default);
        }
        else {
            return \Core\Arr::element($key, $_POST, $default);
        }
    }

    /**
     * Return true if any data was posted, and false if wasn't
     * --
     * @param  string $key Are we looking for particular key?
     * @return boolean
     */
    public function has_post($key = false)
    {
        if ($key)
            { return isset($_POST[$key]); }
        else
            { return !empty($_POST); }
    }

    /**
     * Get request method: Request::METHOD_GET, Request::METHOD_POST,
     *                     Request::METHOD_PUT, Request::METHOD_DELETE
     * --
     * @return integer
     */
    public function get_method()
    {
        // Is it put?
        if ($_SERVER['REQUEST_METHOD'] === 'PUT' ||
            $this->post('REQUEST_METHOD') === 'PUT') {
            return self::METHOD_PUT;
        }

        // Is it delete?
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' ||
            $this->post('REQUEST_METHOD') === 'DELETE' ||
            $this->get('request_method') === 'delete') {
            return self::METHOD_DELETE;
        }

        // Is it post?
        if ($this->has_post()) {
            return self::METHOD_POST;
        }

        // It must be get.
        return self::METHOD_GET;
    }
}
