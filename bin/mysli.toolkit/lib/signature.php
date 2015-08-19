<?php

namespace mysli\toolkit; class signature
{
    /**
     * Create a signature on string.
     * --
     * @param  string $string
     * @param  string $key
     * --
     * @return string
     */
    static function create($string, $key)
    {
        $checksum = sha1($string.$key);
        return "{$checksum}%{$string}";
    }

    /**
     * Check weather string is signed at all.
     * --
     * @param string $string
     * --
     * @return boolean
     */
    static function has($string)
    {
        return !!static::get($string);
    }

    /**
     * Get string's signature.
     * --
     * @param string $string
     * --
     * @return string
     */
    static function get($string)
    {
        if (!strpos($string, '%'))
            return;

        list($signature, $_) = explode('%', $string, 2);

        if (strlen($signature) === 40)
            return $signature;
    }

    /**
     * Check weather signature is valid.
     * --
     * @param string $string
     * @param string $key
     * --
     * @return boolean
     */
    static function is_valid($string, $key)
    {
        $sign = static::get($string);

        if (!$sign)
            return false;

        return static::create($string, $key) === $sign;
    }

    /**
     * Strip signature off string, --- return string without signature.
     * --
     * @param string $string
     * --
     * @return string
     */
    static function strip($string)
    {
        if (($sign = static::get($string)))
        {
            return substr($string, strlen($sign));
        }
        else
        {
            return $string;
        }
    }
}
