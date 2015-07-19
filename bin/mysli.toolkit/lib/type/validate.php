<?php

namespace mysli\toolkit\type; class validate
{
    const __use = '.{ exception.validate }';

    /**
     * Check weather input is an integer, and if not throw an exception.
     * --
     * @throws mysli\toolkit\exception\validate
     *         720 Unexpected type, expected an integer.
     * --
     * @param mixed   $input
     * @param integer $min optional, minimum integer size.
     * @param integer $max optional, maximum integer size.
     * @param string  $message
     */
    static function need_int($input, $min=null, $max=null, $message=null)
    {
        if (!is_integer($input))
        {
            throw new exception\validate(
                ($message ?: "Unexpected type, expected an integer."), 720
            );
        }

        self::need_int_range($input, $min, $max, $message);
    }

    /**
     * Check weather integer is in particular range,
     * and if not, throw an exception.
     * --
     * @throws mysli\toolkit\exception\validate
     *         721 Unexpected value, expected at lest $min.
     *
     * @throws mysli\toolkit\exception\validate
     *         722 Unexpected value, expected not more than $max.
     * --
     * @param integer $input
     * @param integer $min
     * @param integer $max
     * @param string  $message
     */
    static function need_int_range($input, $min=null, $max=null, $message=null)
    {
        if ($min !== null && $input < $min)
        {
            throw new exception\validate(
                ($message ?: "Unexpected value, expected at lest `{$min}`."),
                721
            );
        }

        if ($max !== null && $input > $max)
        {
            throw new exception\validate(
                ($message ?: "Unexpected value, expected not more than `{$max}`."),
                722
            );
        }
    }

    /**
     * Check weather input is string and if not, throw an exception.
     * --
     * @throws mysli\toolkit\exception\validate
     *         723 Unexpected type, expected a string.
     * --
     * @param mixed  $input
     * @param string $message
     */
    static function need_str($input, $message=null)
    {
        if (!is_string($input) && !is_null($input)) {
            throw new exception\validate(
                ($message ?:
                    "Unexpected type, expected a string, got: `".
                    gettype($input) . "`."
                ),
                723
            );
        }
    }

    /**
     * Check weather input is string or integer if not, throw an exception.
     * --
     * @throws mysli\toolkit\exception\validate
     *         724 Unexpected type, expected an integer or a string.
     * --
     * @param mixed  $input
     * @param string $message
     */
    static function need_str_or_int($input, $message=null)
    {
        if (!is_integer($input) && !is_string($input))
        {
            throw new exception\validate(
                ($message ?: "Unexpected type, expected an integer or a string."),
                724
            );
        }
    }

    /**
     * Check weather input is callable, if not, throw an exception.
     * --
     * @throws mysli\toolkit\exception\validate
     *         725 Unexpected value, needs to be callable!
     * --
     * @param mixed  $input
     * @param string $message
     */
    static function need_callable($input, $message=null)
    {
        if (!is_callable($input))
        {
            throw new exception\validate(
                ($message ?: "Unexpected value, needs to be callable!"), 725
            );
        }
    }
}
