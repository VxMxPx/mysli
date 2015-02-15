<?php

namespace mysli\util\output;

__use(__namespace__, '
    mysli.web.html
');

class output {

    private static $outputs = [];

    /**
     * Add (replace if exists) output string
     * @param string  $contents
     * @param mixed   $key
     */
    static function add($contents, $key='%master') {
        self::$outputs[$key] = $contents;
    }
    /**
     * Append output string (if key already exists)
     * @param  string  $contents
     * @param  boolean $key
     */
    static function append($contents, $key='%master') {
        if (isset(self::$outputs[$key])) {
            $contents = self::$outputs[$key] . $contents;
        }
        self::$add($contents, $key);
    }
    /**
     * Prepend output string (if key already exists)
     * @param  string  $contents
     * @param  boolean $key
     */
    static function prepend($contents, $key='%master') {
        if (isset(self::$outputs[$key])) {
            $contents = $contents . self::$outputs[$key];
        }
        self::$add($contents, $key);
    }
    /**
     * Will take particular output (it will return it, and then erase it)
     * @param   string  $key get particular output item, if not provided,
     *                       all will be returned.
     * @return  mixed
     */
    static function take($key=false) {
        $output = self::as_string($key);
        self::clear($key);
        return $output;
    }
    /**
     * Return one part or whole output as a string (will escape HTML tags).
     * @param  mixed $key
     * @return string or null if not found.
     */
    static function as_string($key=false) {
        if (!$key) {
            $html = implode("\n", self::$outputs);
        } else {
            if (isset(self::$outputs[$key])) {
                $html = self::$outputs[$key];
            } else return;
        }
        return html::entities_encode($html);
    }
    /**
     * Return one part or whole output as a HTML.
     * @param  mixed $key
     * @return string or null if not found
     */
    static function as_html($key=false) {
        if (!$key) {
            return implode("\n", self::$outputs);
        } elseif (isset(self::$outputs[$key])) {
            return self::$outputs[$key];
        } else return;
    }
    /**
     * Return all output items as an array.
     * @return array
     */
    static function as_array() {
        return self::$outputs;
    }
    /**
     * Do we have particular key, or any output at all?
     * @param   string  $key
     * @return  boolean
     */
    static function has($key=false) {
        if (!$key) {
            return empty(self::$outputs);
        } else {
            return isset(self::$outputs[$key]);
        }
    }
    /**
     * Clear Output. If key is provided only particular item will be cleared.
     * Otherwise all outputs will be cleared.
     * @param   string  $key
     */
    static function clear($key=false) {
        if (!$key) {
            self::$outputs = [];
        } else {
            if (isset(self::$outputs[$key])) {
                unset(self::$outputs[$key]);
            }
        }
    }
}
