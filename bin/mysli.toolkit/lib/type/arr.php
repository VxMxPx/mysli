<?php

namespace mysli\toolkit\type; class arr
{
    const __use = '
        .{
            type.str       -> str,
            type.validate -> validate,
            exception.arr
        }
    ';

    const pad_left = 0;
    const pad_right = 1;
    const pad_both = 2;

    // Merge only associative arrays, otherwise append.
    const merge_associative = 'arr::merge_associative';
    // Merge non-associative arrays.
    const merge_all = 'arr::merge_all';

    /**
     * Returns an array using the values of array as keys
     * and their frequency in array as values.
     * --
     * @throws mysli\toolkit\exception\validate
     *         724 Unexpected type, expected an integer or a string.
     * --
     * @param  array   $array
     * @param  boolean $case_sensitive
     * --
     * @return array
     */
    static function count_values(array $array, $case_sensitive=true)
    {
        if ($case_sensitive && function_exists('array_count_values'))
        {
            return array_count_values($array);
        }

        $count = [];

        foreach ($array as $value)
        {
            validate::need_str_or_int($value);

            if (!$case_sensitive && is_string($value))
            {
                $value = mb_strtolower($value, 'UTF-8');
            }

            $count[$value] = self::key_in($count, $value)
                ? $count[$value]+1
                : 1;
        }

        return $count;
    }

    /**
     * Retrun number of particular value.
     * --
     * @param  array   $array
     * @param  string  $value
     * @param  boolean $case_sensitive
     * --
     * @return integer
     */
    static function count_values_of(array $array, $value, $case_sensitive=true)
    {
        $values = self::count_values($array, $case_sensitive);
        return isset($values[$value]) ? $values[$value] : 0;
    }

    /**
     * Remove array element(s), by value and return new array.
     * --
     * @throws mysli\toolkit\exception\arr 10 Invalid parameters.
     * --
     * @param array $array
     * @param mixed $value
     *
     * @param integer $limit
     *        true delete only first element,
     *        fale delete all elements that are found.
     *
     * @param boolean $strict
     *        If true it will search for identical elements
     *        this means it will also check the types of the value in the array,
     *        and objects must be the same instance.
     * --
     * @return array
     */
    static function delete_by_value(array $array, $value, $limit=true, $strict=false)
    {
        while (($key = array_search($value, $array, $strict)) !== false)
        {
            if ($key === null)
            {
                throw new exception\arr("Invalid parameters.", 10);
            }

            unset($array[$key]);

            if ($limit)
            {
                break;
            }
        }

        return $array;
    }

    /**
     * Merge one or more arrays.
     * --
     * @param array  $...
     *        At least two arrays to be merged.
     *
     * @param string $...
     *        Optional, used to set merge strategy:
     *        - [arr::merge_associative] - merge only associative arrays,
     *                                     otherwise append.
     *        - arr::merge_all           - merge non-associative arrays too,
     * --
     * @throws mysli\toolkit\exception\arr
     *         10 At least 2 parameters are required.
     *
     * @throws mysli\toolkit\exception\arr
     *         20 "Invalid merge type.
     *
     * @throws mysli\toolkit\exception\arr
     *         30 "All parameters except last, needs to be an array.
     * --
     * @return array
     */
    static function merge()
    {
        $arguments = func_get_args();

        if (count($arguments) < 2)
        {
            throw new exception\arr(
                "At least 2 parameters are required.", 10
            );
        }

        if (is_string(self::last($arguments)))
        {
            $type = array_pop($arguments);

            if (!in_array($type, [self::merge_associative, self::merge_all]))
            {
                throw new exception\arr("Invalid merge type.", 20);
            }
        }
        else
        {
            $type = self::merge_associative;
        }

        foreach ($arguments as $arg)
        {
            if (!is_array($arg))
            {
                throw new exception\arr(
                    "All parameters except last, needs to be an array.", 30
                );
            }
        }

        $result = [];

        foreach ($arguments as $array)
        {
            if (empty($array))
            {
                continue;
            }

            // if merge all, then this will always be true
            $is_associative = $type === self::merge_all || self::is_associative($array);

            foreach ($array as $key => $item)
            {
                if (!$is_associative)
                {
                    $result[] = $item;
                    continue;
                }

                if (!array_key_exists($key, $result))
                {
                    $result[$key] = $item;
                    continue;
                }
                else
                {
                    if (!is_array($result[$key]) && !is_array($item))
                    {
                        $result[$key] = $item;
                    }
                    else
                    {
                        if (!is_array($result[$key]))
                        {
                            $result[$key] = [$result[$key]];
                        }

                        if (!is_array($item))
                        {
                            $item = [$item];
                        }

                        $result[$key] = self::merge($result[$key], $item, $type);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Pad array to the specified length with a value.
     * --
     * @throws mysli\toolkit\exception\arr
     *         10 Invalid type, required:
     *         arr::pad_left | arr::pad_right | arr::pad_both.
     *
     * @throws mysli\toolkit\exception\validate
     *         720 Unexpected type, expected an integer.
     * --
     * @param array   $array
     * @param mixed   $value
     * @param integer $size Negative values are not acceptable.
     * @param integer $type arr::pad_left, arr::pad_right, arr::pad_both
     * --
     * @return array
     */
    static function pad(array $array, $value, $size, $type=self::pad_right)
    {
        validate::need_int($size, 1);

        switch ($type)
        {
            case self::pad_left:
                return array_pad($array, $size, $value);

            case self::pad_right:
                return array_pad($array, -($size), $value);

            case self::pad_both:
                $count = count($array);
                $diff = $size - $count;
                if ($diff < 1)
                {
                    return $array;
                }
                else
                {
                    $slice = ceil($diff / 2) + $count;
                    $array = array_pad($array, -($slice), $value);
                    return array_pad($array, $size, $value);
                }
                break;

            default:
                throw new exception\arr(
                    "Invalid type: `{$type}`, required: ".
                    "arr::pad_left | arr::pad_right | arr::pad_both", 10
                );
        }
    }

    /**
     * Check if the array is associative.
     * http://stackoverflow.com/questions/173400/php-arrays-a-good-way-
     * to-check-if-an-array-is-associative-or-sequential
     * --
     * @param array $array
     * --
     * @return boolean
     */
    static function is_associative(array $array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Check if the required key is exists.
     * This is using array_key_exists so key => null will return true.
     * --
     * @throws mysli\toolkit\exception\validate
     *         724 Unexpected type, expected an integer or a string.
     * --
     * @param array $array
     *
     * @param mixed $key
     *        integer (check index),
     *        string (check key),
     *        array (check multiple keys)
     * --
     * @return boolean
     */
    static function key_in(array $array, $key)
    {
        if (empty($array))
        {
            return false;
        }

        if (!is_array($key))
        {
            validate::need_str_or_int($key);
            return array_key_exists($key, $array);
        }

        foreach ($key as $ck)
        {
            validate::need_str_or_int($ck);
            if (!array_key_exists($ck, $array))
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Get first element from array.
     * --
     * @param array $array
     * --
     * @return mixed (null if not found)
     */
    static function first(array $array)
    {
        if (empty($array))
        {
            return null;
        }
        else
        {
            return reset($array);
        }
    }

    /**
     * Get last element from array.
     * --
     * @param array $array
     * --
     * @return mixed (null if not found)
     */
    static function last(array $array)
    {
        if (empty($array))
        {
            return null;
        }
        else
        {
            return end($array);
        }
    }

    /**
     * Get first key from array.
     * --
     * @param array $array
     * --
     * @return mixed string | integer | false
     */
    static function first_key(array $array)
    {
        reset($array);
        return key($array);
    }

    /**
     * Get last key from array.
     * --
     * @param  array $array
     * --
     * @return mixed string | integer
     */
    static function last_key(array $array)
    {
        end($array);
        return key($array);
    }

    /**
     * Get one or more random elements out of an array.
     * --
     * @param array $array
     * --
     * @return array
     */
    static function get_random(array $array)
    {
        shuffle($array);
        return array_pop($array);
    }

    /**
     * Get particular element out of array, of return default
     * if element doesn't exists.
     * --
     * @param array  $array
     * @param string $key
     * @param mixed  $default
     * --
     * @return mixed
     */
    static function get(array $array, $key, $default=null)
    {
        if (!self::key_in($array, $key))
        {
            return $default;
        }
        else
        {
            return $array[$key];
        }
    }

    /**
     * Get multiple elements out of array, or return default if elements
     * doesn't exists. This will build new array, containing required keys.
     * --
     * @param array $array
     *
     * @param mixed $keys
     *        An array or string with comma separated keys.
     *
     * @param mixed $default
     *        An array to set default for each key, or a
     *        single value which will be applied for all missing entires.
     * --
     * @return array
     */
    static function get_all(array $array, $keys, $default=null)
    {
        if (is_string($keys))
        {
            $keys = str::split_trim($keys, ',');
        }

        if (!is_array($default))
        {
            $default = self::pad([], $default, count($keys));
        }

        if (empty($keys))
        {
            return $default;
        }

        $result = [];

        foreach ($keys as $key)
        {
            $result[] = self::get($array, $key, array_shift($default));
        }

        return $result;
    }

    /**
     * Find a value in an array and return corresponding key.
     * --
     * @param array   $array
     * @param mixed   $value
     * @param boolean $strict
     *        Should strict comparison (===) be used.
     * --
     * @return mixed
     *         If $limit then string | integer, false if not found.
     *         If $limit = false then array.
     */
    static function find(array $array, $value, $limit=true, $strict=false)
    {
        if ($limit)
        {
            $return = array_search($value, $array, $strict);

            if ($return === null)
            {
                throw new exception\arr(
                    "Invalid parameters.", 10
                );
            }

            return $return;
        }
        else
        {
            return array_keys($array, $value, $strict);
        }
    }

    /**
     * Append an item to the end of an array.
     * This will modify original array!
     * --
     * @param array $array
     * @param mixed $value
     * --
     * @return null
     */
    static function append(array &$array, $value)
    {
        $array[] = $value;
    }

    /**
     * Perpend an item the the beginning of an array.
     * This will modify original array!
     * --
     * @param array $array
     * @param mixed $value
     * --
     * @return null
     */
    static function prepend(array &$array, $value)
    {
        array_unshift($array, $value);
    }

    /**
     * Insert an item into array at particular position.
     * This will modify original array!
     * --
     * @throws mysli\toolkit\exception\validate
     *         720 Unexpected type, expected an integer.
     * --
     * @param array   $array
     * @param mixed   $value
     * @param integer $position
     * --
     * @return null
     */
    static function insert(array &$array, $value, $position)
    {
        validate::need_int($position);

        if ($position < 0)
        {
            $position = count($array) + $position;
            if ($position < 0)
            {
                $position = 0;
            }
        }

        if ($position > 0)
        {
            $a1 = array_slice($array, 0, $position);
            $a1[] = $value;
            $array = array_merge($a1, array_slice($array, $position));
        }
        else
        {
            self::prepend($array, $value);
        }
    }

    /**
     * Implode array's keys.
     * --
     * @throws mysli\toolkit\exception\validate
     *         724 Unexpected type, expected an integer or a string.
     * --
     * @param array  $array
     * @param string $glue
     * --
     * @return string
     */
    static function implode_keys(array $array, $glue)
    {
        validate::need_str_or_int($glue);
        return implode($glue, array_keys($array));
    }

    /**
     * Return array as a nicely formatted string. Example:
     * key      : value
     * long_key : value
     * --
     * @throws mysli\toolkit\exception\validate
     *         720 Unexpected type, expected an integer.
     *
     * @throws mysli\toolkit\exception\validate
     *         723 Unexpected type, expected a string.
     * --
     * @param array $array
     *
     * @param integer $indent
     *        Starting indentation.
     *
     * @param integer $step
     *        In multi-dimensional array, how much to indent the next level.
     *
     * @param string $separator
     *        Used to separate key and value.
     *
     * @param string $new_line
     *        New line character.
     *
     * @param boolean $valuefy
     *        Print bolean values as true/false, and string values as "value".
     * --
     * @return string
     */
    static function readable(
        array $array,
        $indent=0,
        $step=2,
        $separator=' : ',
        $new_line="\n",
        $valuefy=false)
    {
        validate::need_int($indent, 0);
        validate::need_int($step, 0, null, 1);
        validate::need_str($separator, 2);
        validate::need_str($new_line, 3);

        $long_key = 0;
        $out = '';

        // Get the longest key...
        foreach ($array as $key => $val)
        {
            if (str::length($key) > $long_key)
            {
                $long_key = str::length($key);
            }
        }

        foreach ($array as $key => $value)
        {
            $out .= str_repeat(' ', $indent) . $key;

            if (is_array($value))
            {
                if (!empty($value))
                {
                    $out .= $new_line . self::readable(
                        $value, $indent + $step, $step, $separator,
                        $new_line, $valuefy
                    );
                }
                else
                {
                    $out .= str_repeat(' ', $long_key - str::length($key)) .
                        $separator . '[]';
                }
            }
            else
            {
                if ($valuefy)
                {
                    if (is_bool($value))
                    {
                        $value = $value ? 'true' : 'false';
                    }
                    elseif (is_string($value))
                    {
                        $value = '"'.$value.'"';
                    }
                }

                $out .= str_repeat(' ', $long_key - str::length($key)) .
                    $separator . $value;
            }

            $out .= $new_line;
        }

        return rtrim($out);
    }

    /**
     * The same as `readable` return an array as a nicely formatted string.
     * This will ignore keys though, and return only list of values.
     * Example:
     * - value
     * - value
     *     - sub-value
     *     - sub-value
     * --
     * @throws mysli\toolkit\exception\validate
     *         720 Unexpected type, expected an integer.
     *
     * @throws mysli\toolkit\exception\validate
     *         723 Unexpected type, expected a string.
     * --
     * @param array $array
     *
     * @param integer $indent
     *        Starting indentation.
     *
     * @param integer $step
     *        In multi-dimensional array, how much to indent the next level.
     *
     * @param string $marker
     *        Used to mark a new line (e.g. -)
     *
     * @param string $new_line
     *        New line character.
     *
     * @param boolean $valuefy
     *        Print bolean values as true/false, and string values as "value"
     * --
     * @return string
     */
    static function readable_list(
        array $array,
        $indent=0,
        $step=2,
        $marker='- ',
        $new_line="\n",
        $valuefy=false)
    {
        validate::need_int($indent, 0);
        validate::need_int($step, 0, null, 1);
        validate::need_str($marker, 2);
        validate::need_str($new_line, 3);

        $out = '';

        foreach ($array as $value)
        {
            $out .= str_repeat(' ', $indent) . $marker;

            if (is_array($value))
            {
                $out .= $new_line . self::readable_list(
                    $value, $indent + $step, $step, $marker, $new_line, $valuefy
                );
            }
            else
            {
                if ($valuefy)
                {
                    if (is_bool($value))
                    {
                        $value = $value ? 'true' : 'false';
                    }
                    elseif (is_string($value))
                    {
                        $value = '"'.$value.'"';
                    }
                }

                $out .= $value;
            }

            $out .= $new_line;
        }

        return rtrim($out);
    }

    /**
     * Set key from array value.
     * $array = [0 => '12.Inna', 1 => '23.Marko']
     * $separator = .
     * return = [12 => 'Inna', '23' => 'Marko']
     * --
     * @throws mysli\toolkit\exception\validate
     *         723 Unexpected type, expected a string.
     * --
     * @param array   $array
     * @param string  $separator
     * @param boolean $skip_missing
     *        If no key found, skip item rather than adding it the list
     *        with default (numeric) id.
     * --
     * @return array
     */
    static function split_to_key(array $array, $separator=':', $skip_missing=true)
    {
        validate::need_str($separator);
        $return = [];

        foreach($array as $val)
        {
            $value = explode($separator, $val, 2);
            if (self::key_in($value, 0) && self::key_in($value, 1))
            {
                $return[trim($value[0])] = trim($value[1]);
            }
            elseif (!$skip_missing)
            {
                $return[] = $val;
            }
        }

        return $return;
    }

    /**
     * Trim values.
     * --
     * @throws mysli\toolkit\exception\validate
     *         723 Unexpected type, expected a string.
     * --
     * @param array  $array
     * @param string $mask
     * --
     * @return array
     */
    static function trim_values(array $array, $mask=null)
    {
        if (!is_null($mask))
        {
            validate::need_str($mask);
        }

        foreach ($array as $key => $val)
        {
            if ($mask)
            {
                $array[$key] = trim($val, $mask);
            }
            else
            {
                $array[$key] = trim($val);
            }
        }

        return $array;
    }
}