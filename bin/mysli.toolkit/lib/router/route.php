<?php

namespace mysli\toolkit\router; class route
{
    const __use = '.{ type.arr -> arr, exception.router }';

    protected $uri;
    protected $method;
    protected $container = [
        'get'     => [],
        'post'    => [],
        'option'  => [],
        'segment' => [],
    ];

    /**
     * Instance of route.
     * --
     * @param string $uri
     * @param string $method
     * @param array  $get
     * @param array  $post
     */
    function __construct($uri, $method, $get, $post)
    {
        $this->set_uri($uri);
        $this->set_method($method);
        $this->set_get($get);
        $this->set_post($post);
    }

    /*
    --- Uri --------------------------------------------------------------------
     */

    /**
     * Get currently set URI.
     * --
     * @return string
     */
    function uri()
    {
        return $this->uri;
    }

    /**
     * Set a new URI.
     * --
     * @param string $uri
     */
    function set_uri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Get current route's method.
     * POST|GET|PUT|DELETE
     * --
     * @return string
     */
    function method()
    {
        return $this->method;
    }

    /**
     * Set current route's method.
     * --
     * @param string $method
     */
    function set_method($method)
    {
        $this->method = $method;
    }

    /*
    --- Get, Post, Option, Segment ---------------------------------------------
     */

    /**
     * Call one of the standard methods.
     * --
     * @param string $method
     * @param array  $args
     * --
     * @throws mysli\toolkit\exception\router 10 Invalid method.
     * --
     * @return mixed
     */
    function __call($method, $args)
    {
        if (strpos($method, '_'))
        {
            $action = substr($method, 0, 4);
            $id = substr($method, 4);
        }
        else
        {
            $action = '';
            $id = $method;
        }

        if (!array_key_exists($id, $this->container))
        {
            throw new exception\router("Invalid method: `{$method}`.", 10);
        }

        array_unshift($args, $id);
        return call_user_func_array([$this, "{$action}generic"], $args);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Acquire a value from domain.
     * (@see arr::get()) and (@see arr::get_all())
     * --
     * @param string $domain
     *
     * @param mixed $key
     *        Couple of different types are accepted as a key:
     *        null   - will return all values,
     *        string - return specific value or default,
     *        array  - return multiple values as an array.
     *                 If array of the same length is set as default,
     *                 corresponding value from it will be returned when required
     *                 not found.
     *
     * @param mixed $default
     * --
     * @return mixed
     */
    protected function generic($domain, $key=null, $default=null)
    {
        if (is_null($key))
            return $this->container[$domain];
        if (is_array($key))
            return arr::get_all($this->container[$domain], $key, $default);
        else
            return arr::get($this->container[$domain], $key, $default);
    }

    /**
     * Check if key exists, or if key not provided, check if there's any value
     * on whole domain.
     * --
     * @param string $key
     * --
     * @return boolean
     */
    protected function has_generic($key=null)
    {
        if (is_null($key))
            return !!count($this->container[$domain]);
        else
            return array_key_exists($key, $this->container[$domain]);
    }

    /**
     * Set a new value on domain.
     * --
     * @param string $domain
     *
     * @param mixed $key
     *        Following types are accepted:
     *        string - set a single value for a key
     *        array  - set multiple values (merge arrays) if $value is null,
     *                 if $value is false, erase existing array and set current.
     *
     * @param mixed $value
     */
    protected function set_generic($domain, $key, $value=null)
    {
        if (is_array($key))
        {
            if (is_null($value))
                $this->{$domain} = array_merge(
                    $this->container[$domain],
                    $key
                );
            else
                $this->container[$domain] = $key;
        }
        else
            $this->container[$domain][$key] = $value;
    }
}
