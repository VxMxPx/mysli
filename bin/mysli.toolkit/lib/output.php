<?php

namespace mysli\toolkit; class output
{
    const __use = '.html';

    /**
     * Contains all outputs that were set so far.
     * --
     * @var array
     */
    private static $outputs = [];

    /**
     * Set (replace if exists) output string.
     * --
     * @param string $output
     * @param string $key
     */
    static function set($output, $key='%master')
    {
        \log::debug("Output set: `{$key}`.", __CLASS__);
        static::$outputs[$key] = $output;
    }

    /**
     * Append output string (if key already exists).
     * --
     * @param string $output
     * @param string $key
     */
    static function append($output, $key='%master')
    {
        if (isset(static::$outputs[$key]))
        {
            $output = static::$outputs[$key] . $output;
        }

        static::set($output, $key);
    }

    /**
     * Prepend output string (if key already exists).
     * --
     * @param string  $output
     * @param boolean $key
     */
    static function prepend($output, $key='%master')
    {
        if (isset(static::$outputs[$key]))
        {
            $output = $output . static::$outputs[$key];
        }

        static::set($output, $key);
    }

    /**
     * Take a particular output (it will return it, and then erase it).
     * --
     * @param string $key
     *        Get a particular output item, if not provided,
     *        all will be returned.
     * --
     * @return string
     */
    static function take($key=null)
    {
        $output = static::as_html($key);
        static::clear($key);
        return $output;
    }

    /**
     * Return one part or whole output as a string (it escape HTML tags).
     * --
     * @param mixed $key
     * --
     * @return string
     */
    static function as_string($key=null)
    {
        return html::entities_encode(static::as_html($key));
    }

    /**
     * Return one part or whole output as a HTML.
     * --
     * @param mixed $key
     * --
     * @return string
     */
    static function as_html($key=null)
    {
        if (!$key)
        {
            return implode("\n", static::$outputs);
        }
        elseif (isset(static::$outputs[$key]))
        {
            return static::$outputs[$key];
        }
        else
        {
            return;
        }
    }

    /**
     * Return all output items as an array.
     * --
     * @return array
     */
    static function as_array()
    {
        return static::$outputs;
    }

    /**
     * Check if particular key is set.
     * If no key provided, it will check if any output is set.
     * --
     * @param string $key
     * --
     * @return boolean
     */
    static function has($key=null)
    {
        if (!$key)
        {
            return empty(static::$outputs);
        }
        else
        {
            return isset(static::$outputs[$key]);
        }
    }

    /**
     * Clear Output. If key is provided only particular item will be cleared.
     * Otherwise all outputs will be cleared.
     * --
     * @param string $key
     */
    static function clear($key=null)
    {
        if (!$key)
        {
            static::$outputs = [];
        }
        else
        {
            if (isset(static::$outputs[$key]))
            {
                unset(static::$outputs[$key]);
            }
        }
    }
}
