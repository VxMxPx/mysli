<?php

/**
 * # Output
 *
 * Manage outputs. Allows for output to be kept, and set to client all at once,
 * rather than being echoed immediately. It will allow later modifications of
 * output streams if so desired.
 */
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
        self::$outputs[$key] = $output;
    }

    /**
     * Append output string (if key already exists).
     * --
     * @param string $output
     * @param string $key
     */
    static function append($output, $key='%master')
    {
        if (isset(self::$outputs[$key]))
        {
            $output = self::$outputs[$key] . $output;
        }

        self::set($output, $key);
    }

    /**
     * Prepend output string (if key already exists).
     * --
     * @param string  $output
     * @param boolean $key
     */
    static function prepend($output, $key='%master')
    {
        if (isset(self::$outputs[$key]))
        {
            $output = $output . self::$outputs[$key];
        }

        self::set($output, $key);
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
        $output = self::as_html($key);
        self::clear($key);
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
        return html::entities_encode(self::as_html($key));
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
            return implode("\n", self::$outputs);
        }
        elseif (isset(self::$outputs[$key]))
        {
            return self::$outputs[$key];
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
        return self::$outputs;
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
            return empty(self::$outputs);
        }
        else
        {
            return isset(self::$outputs[$key]);
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
            self::$outputs = [];
        }
        else
        {
            if (isset(self::$outputs[$key]))
            {
                unset(self::$outputs[$key]);
            }
        }
    }
}
