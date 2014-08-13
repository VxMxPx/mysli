<?php

namespace mysli\type {
    class arr {

        const case_lower = 0;
        const case_upper = 1;

        /**
         * Changes the case of all keys in an array, UTF-8
         * http://php.net/manual/en/function.array-change-key-case.php#107715
         * @param  array   $arr
         * @param  integer $case
         * @return array
         */
        static function change_key_case(array $array, $case=self::case_lower) {
            $case = ($case == self::case_lower) ? MB_CASE_LOWER : MB_CASE_UPPER;
            foreach ($array as $key => $val) {
                $return[mb_convert_case($key, $case, 'UTF-8')] = $val;
            }
            return $return;
        }
        /**
         * Chunks an array into arrays with size elements. 
         * The last chunk may contain less than size elements.
         * @param  array   $array
         * @param  integer $size
         * @param  boolean $preserve_keys
         * @return array
         */
        static function chunk(array $array , $size, $preserve_keys=false) {
            $size = (int) $size;
            if ($size < 1) {
                throw new exception\parameter("Size is less than 1.");
            }
            return array_chunk($array, $size, $preserve_keys);
        }
        /**
         * Returns the values from a single column of the array, 
         * identified by the column_key. 
         * Optionally, you may provide an index_key to index the values 
         * in the returned array by the values from the index_key column 
         * in the input array.
         * @param  array  $array
         * @param  mixed $column_key
         * @param  mixed $index_key
         * @return array
         */
        static function column(array $array, $column_key, $index_key=null) {
            return array_column($array, $column_key, $index_key);
        }
        /**
         * Creates an array by using the values from the keys array as keys and 
         * the values from the values array as the corresponding values.
         * @param  array  $keys
         * @param  array  $values
         * @return array
         */
        static function combine(array $keys, array $values) {
            if (count($keys) !== count($values)) {
                throw new exception\parameter(
                    "The number of elements for each array must be equal.");
            }
            return array_combine($keys, $values);
        }
        /**
         * Returns an array using the values of array as keys 
         * and their frequency in array as values.
         * @param  array   $array
         * @param  boolean $case_sensitive
         * @return array
         */
        static function count_values(array $array, $case_sensitive=true) {
            $count = [];
            foreach ($array as $value) {
                if (!is_int($value) && !is_string($value)) {
                    throw new exception\parameter(
                        "Array values must be either string or integer.");
                }
                if (!$case_sensitive && is_string($value)) {
                    $value = mb_strtolower($value, 'UTF-8');
                }
                $count[$value]++;
            }
            return $count;
        }
        /**
         * Returns an array containing all the elements of array1 after applying 
         * the callback function to each one. 
         * The number of parameters that the callback function accepts 
         * should match the number of arrays passed to the method.
         * Last argument is callback!
         * @param  array    $...
         * @param  callable $...
         * @return array
         */
        static function map() {
            if (func_num_args() < 2) {
                throw new exception\parameter(
                    "At least two parameters are required. ".
                    "Second must be callable.", 1);
            }
            $arguments = func_get_args();
            $callback = array_pop($arguments);
            if (!is_callable($callback)) {
                throw new exception\parameter(
                    "Last argument needs to be callable.", 2);
            }
            foreach ($arguments as $arg) {
                if (!is_array($arg)) {
                    throw new exception\parameter("Array is required.", 3);
                }
            }
            array_unshift($arguments, $callback);
            return call_user_func_array('array_map', $arguments);
        }
    }
}


class Arr
{
    const CAMELCASE  = 'camelcase';
    const UNDERSCORE = 'underscore';

    /**
     * Multi dimensional array, to one dimension.
     * Pass in value = name
     *     [['id' => 15, 'name' => 'Jack'], ['id' => 42, 'name' => 'Neo']] ---->
     *     ['Jack', 'Neo']
     * Pass in value = id
     *     [15, 42]
     *
     * If you pass in the key, we'll grab item[key] value, and set it as a key.
     * Pass in key = id, value = name
     *     ['id' => 12, 'name' => 'Inna'] ----> [12 => 'Inna']
     * --
     * @param   array   $input_array
     * @param   string  $value   Which sub-key's value, we use as value?
     * @param   string  $key     Which sub-key's value, we use as key?
     * --
     * @return  array
     */
    public static function to_one_dimension(array $input_array, $value, $key = false)
    {
        $result     = [];
        $position   = 0;
        $rewritable = []; // Items which were set from key
                          // and will be rewritten if duplicated.

        if (!is_array($input_array) || empty($input_array)) {
            return [];
        }

        foreach ($input_array as $item)
        {
            if (!array_key_exists($value, $item)) continue;

            if ($key && array_key_exists($key, $item)) {
                if (!in_array($item[$key], $rewritable) && array_key_exists($item[$key], $result)) {
                    $result[$position] = $result[$item[$key]];
                }
                $result[$item[$key]] = $item[$value];
                $rewritable[] = $item[$key];
            } else {
                $result[$position] = $item[$value];
            }

            $position = 0;
            do {
                $position++;
            } while(array_key_exists($position, $result));
        }

        return $result;
    }

    /**
     * Will implode array's keys.
     * --
     * @param   string  $glue
     * @param   array   $pieces
     * --
     * @return  string
     */
    public static function implode_keys($glue, array $pieces)
    {
        return implode($glue, array_keys($pieces));
    }

   /**
     * In multi dimensional arrays, take sub-key, and set it as main key.
     * If we pass in 'id' as '$index_key':
     *     [['id' => 12, 'age' => 25], ['id' => 20, 'age' => 30]] ---->
     *     ['12' => ['id' => 12, 'age' => 25], 20 => ['id' => 20, 'age' => 30]]
     * If key already exists, and rewrite is set to true:
     *     [['id' => 12, 'age' => 25], ['id' => 12, 'age' => 40]] ---->
     *     [12 => ['id' => 12, 'age' => 40]]
     * If rewrite is set to false:
     *     [12 => ['id' => 12, 'age' => 25], 12_1 => ['id' => 12, 'age' => 40]]
     * If automatic is set to true:
     *     [['id' => 12, 'age' => 25], ['age' => 40]] ---->
     *     [12 => ['id' => 12, 'age' => 25], 1 => ['age' => 40]]
     *  If automatic is set to false:
     *     [12 => ['id' => 12, 'age' => 25]]
     * --
     * @param   array   $input_array
     * @param   string  $index_key
     * @param   boolean $automatic   If select key isn't set, should it be set
     *                               automatically? It will be numeric.
     * @param   boolean $rewrite     Rewrite if key exists, else, add number
     *                               after it.
     * --
     * @return  array
     */
    public static function key_from_sub(
        array $input_array,
        $index_key,
        $automatic = true,
        $rewrite = false
    ) {
        if (!is_array($input_array)) { return []; }

        $result = [];

        foreach($input_array as $item)
        {
            if (array_key_exists($index_key, $item))
            {
                if (array_key_exists($item[$index_key], $result) && !$rewrite) {
                    $i = 2;
                    do {
                        $new_key = $item[$index_key].'_'.$i;
                        $i++;
                    }
                    while (array_key_exists($new_key, $result));
                    $result[$new_key] = $item;
                }
                else {
                    $result[$item[$index_key]] = $item;
                }
            }
            elseif ($automatic) {
                $i = 0;
                do {
                    $new_key = $i;
                    $i++;
                }
                while (array_key_exists($new_key, $result));
                $result[$new_key] = $item;
            }
        }

        return $result;
    }

    /**
     * Return array as a nicely formatted string. Example:
     * key      : value
     * long_key : value
     * --
     * @param array   $input
     * @param string  $separator used to separate key and value.
     * @param string  $new_line  new line character.
     * @param integer $step      in multi-dimensional array, how much to indent next level.
     * @param integer $indent    starting indentation.
     * --
     * @return string
     */
    public static function readable(
        array $input,
        $indent = 0,
        $step = 2,
        $separator = ' : ',
        $new_line = "\n"
    ) {
        $lkey = 0;
        $out  = '';

        // Get the longest key...
        foreach ($input as $key => $val) {
            if (strlen($key) > $lkey) {
                $lkey = strlen($key);
            }
        }

        foreach ($input as $key => $value) {
            $out .= str_repeat(' ', $indent) . $key;
            if (is_array($value)) {
                $out .= $new_line . self::readable($value, $indent + $step, $step, $separator, $new_line);
            } else {
                $out .= str_repeat(' ', $lkey - strlen($key)) . $separator . $value;
            }
            $out .= $new_line;
        }

        return rtrim($out);
    }

    /**
     * Remove empty values from Array.
     * --
     * @param   array   $input_array
     * --
     * @return  array
     */
    public static function remove_empty(array $input_array)
    {
        if (!is_array($input_array) || empty($input_array)) { return []; }

        $result = [];
        $position = 0;
        $is_associative = self::is_associative($input_array);

        foreach($input_array as $key => $val)
        {
            if (!$is_associative) {
                $key = $position;
            }
            if (is_array($val) && !empty($val)) {
                $result_sub = self::remove_empty($val);
                if (!empty($result_sub)) {
                    $result[$key] = $result_sub;
                } else {
                    continue;
                }
            } elseif (is_object($val) || is_bool($val) || is_numeric($val)) {
                $result[$key] = $val;
            } elseif (is_string($val)) {
                if (trim($val)) {
                    $result[$key] = $val;
                } else {
                    // Position counter will not be increased
                    continue;
                }
            } elseif ($val) {
                $result[$key] = $val;
            } else {
                continue;
            }
            $position++;
        }

        return $result;
    }

    /**
     * Check if the array is associative.
     * Source:
     * http://stackoverflow.com/questions/173400/php-arrays-a-good-way-to-check-
     * if-an-array-is-associative-or-sequential
     * --
     * @param  array  $input_array
     * --
     * @return boolean
     */
    public static function is_associative(array $input_array)
    {
        return (bool) count(array_filter(array_keys($input_array), 'is_string'));
    }

    /**
     * Set key from array value.
     * If we set separator to '.':
     * [0 => '12.Inna', 1 => '23.Neo'] ----> [12 => 'Inna', '23' => 'Neo']
     * --
     * @param   array   $input_array
     * @param   string  $separator
     * @param   boolean $ignore_non_existent When no key found, should be item
     *                                       added to the list with default key.
     * --
     * @return  array
     */
    public static function explode_to_key(
        array $input_array,
        $separator = ':',
        $ignore_non_existent = true
    ) {
        if (!is_array($input_array) || empty($input_array)) {
            return [];
        }

        $new_array = [];

        foreach($input_array as $val) {
            $value = explode($separator, $val, 2);

            if (array_key_exists(0, $value) && array_key_exists(1, $value)) {
                $new_array[trim($value[0])] = trim($value[1]);
            } elseif (!$ignore_non_existent) {
                $new_array[] = $val;
            }
        }

        return $new_array;
    }

    /**
     * Clean array keys - remove spaces, dashes, etc...
     * --
     * @param   array   $input_array
     * @param   integer $divider    Arr::CAMELCASE | Arr::UNDERSCORE
     * --
     * @return  array
     */
    public static function clean_keys(array $input_array, $divider = self::UNDERSCORE)
    {
        $new_array = [];
        $to_space  = ['_', '-', '.', ',', ';', '+', '/'];
        $position  = 0;

        foreach ($input_array as $key => $val)
        {
            // Convert some common characters to spaces
            $key = str_replace($to_space, ' ', $key);

            // There should be maximum one space
            $key = Str::limit_repeat($key, ' ', 1);
            $key = Str::normalize($key);
            $key = Str::clean($key, 'aA1s');
            $key = strtolower(trim($key));
            $key = str_replace(' ', '_', $key);

            if (empty($key)) {
                $key = $position;
            } else {
                if ($divider === self::CAMELCASE) {
                    $key = Str::to_camelcase($key, false);
                }
            }

            $new_array[$key] = $val;

            while (array_key_exists($position, $new_array)) {
                $position++;
            }
        }

        return $new_array;
    }

    /**
     * Check if is valid array, if is not empty.
     * --
     * @param   mixed   $input_array
     * --
     * @return  boolean
     */
    public static function is_empty($input_array)
    {
        return !is_array($input_array) || empty($input_array);
    }

    /**
     * Check if input is array and if the required key is exists.
     * This is using array_key_exists so key => null will return true.
     * --
     * @param  mixed  $input_array
     * @param  string $key
     * --
     * @return boolean
     */
    public static function has_key($input_array, $key)
    {
        if (self::is_empty($input_array)) { return false; }
        return array_key_exists($key, $input_array);
    }

    /**
     * Check if array has ALL required keys. Returns true only if all keys are
     * found, and false otherwise.
     * This is using array_key_exists so key => null will return true.
     * --
     * @param  array  $input_array
     * @param  array  $keys
     * --
     * @return boolean
     */
    public static function has_keys(array $input_array, array $keys)
    {
        if (self::is_empty($input_array) || self::is_empty($keys)) {
            return false;
        }

        foreach ($keys as $key) {
            if (!array_key_exists($key, $input_array)) { return false; }
        }

        return true;
    }

    /**
     * Get particular element out of array, of return default if element doesn't
     * exists, or if passed in array is not valid.
     * --
     * @param  string  $key
     * @param  array   $input_array
     * @param  mixed   $default
     * --
     * @return mixed
     */
    public static function element($key, array $input_array, $default = false)
    {
        if (self::is_empty($input_array))
            { return $default; }

        if (!self::has_key($input_array, $key))
            { return $default; }
        else
            { return $input_array[$key]; }
    }

    /**
     * Get particular elements out of array, or return default if element doesn't
     * exists, or if passed in array is not valid. This will build new array,
     * with required keys in it.
     * --
     * @param  mixed $keys        Can be array or string with comma separated keys.
     * @param  array $input_array
     * @param  mixed $default     Can be array to set default for each key.
     * --
     * @return array
     */
    public static function elements($keys, array $input_array, $default = false)
    {
        // If we have string we can covert it to an array
        if (!is_array($keys)) { $keys = Str::explode_trim(',', $keys); }

        // If nothing in array, then we'll just return default
        if (self::is_empty($keys)) { return $default; }

        // Do we have any input and is it an array?
        if (!is_array($input_array)) { $input_array = array($input_array); }

        // Set empty results
        $result = array();

        // Check each key, to see if is set, - if not we'll return default
        foreach ($keys as $i => $key) {

            // If we're having default values as array, we'll check them now
            if (is_array($default)) {
                if (array_key_exists($key, $default)) {
                    $default_current = $default[$key];
                } else {
                    $default_current = false;
                }
            }
            else {
                $default_current = $default;
            }

            // Set result
            $result[$key] = array_key_exists($key, $input_array)
                ? $input_array[$key]
                : $default_current;
        }

        return $result;
    }

    /**
     * Returns random array element.
     * --
     * @param  array $input_array
     * --
     * @return mixed
     */
    public static function random_element(array $input_array)
    {
        if (self::is_empty($input_array)) { return false; }

        shuffle($input_array);
        return array_pop($input_array);
    }

    /**
     * Better merge, will keep values in sub-arrays.
     * --
     * @param   array ...
     * --
     * @return  array
     */
    public static function merge()
    {
        $result     = [];
        $all_arrays = func_get_args();

        foreach ($all_arrays as $array) {

            $is_associative = self::is_associative($array);

            // If Is not an array, then we'll just skip it
            if (self::is_empty($array)) { continue; }

            foreach ($array as $key => $item) {

                if (!$is_associative) {
                    $result[] = $item;
                    continue;
                }

                if (!array_key_exists($key, $result)) {
                    $result[$key] = $item;
                    continue;
                }
                else {
                    if (!is_array($item)) {
                        $result[$key] = $item;
                    }
                    else {
                        $result[$key] = self::merge($result[$key], $item);
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Will implode only values of array, which results in true, - so, empty,
     * null, and false elements won't be imploded.
     * --
     * @param  string $glue
     * @param  array  $pieces
     * @param  mixed  $default In case there's no 'true' values in an array,
     *                         the default value can be returned.
     * --
     * @return string
     */
    public static function implode_true($glue, array $pieces, $default=null)
    {
        $new_array = [];

        foreach ($pieces as $piece) {
            if ($piece) {
                if (is_array($piece)) {
                    $temp = self::implode_true($glue, $piece, false);
                    if ($temp) {
                        $new_array[] = $temp;
                    }
                } else {
                    $new_array[] = $piece;
                }
            }
        }

        if (!empty($new_array)) {
            return implode($glue, $new_array);
        }
        else {
            return $default;
        }
    }

    /**
     * Will get array value by entering string path.
     * ['user' => ['address' => 'My Address']]
     *     ----> user/address
     *     ----> My Address
     * --
     * @param   string  $path
     * @param   array   $input_array
     * @param   mixed   $default
     * --
     * @return  mixed
     */
    public static function get_by_path($path, array $input_array, $default = null)
    {
        if (self::is_empty($input_array)) { return $default; }

        $path = trim($path, '/');
        $path = explode('/', $path);
        $get  = $input_array;

        foreach ($path as $w) {
            if (array_key_exists($w, $get) && $get[$w] !== null) {
                $get = $get[$w];
            } else {
                return $default;
            }
        }

        return $get;
    }

    /**
     * Will set array value by entering path.
     * ['user' => ['address' => 'My Address']]
     *     ----> user/address, 'New Address'
     *     ----> ['user' => ['address' => 'New Address']]
     * --
     * @param   string  $path
     * @param   mixed   $value
     * @param   array   $input_array  Passed as reference
     * --
     * @return  void
     */
    public static function set_by_path($path, $value, &$input_array)
    {
        $what = trim($path, '/');
        $what = explode('/', $what);
        $previous = $value;
        $new      = array();

        for ($i=count($what); $i--; $i==0) {
                $w = $what[$i];
                $new[$w]  = $previous;
                $previous = $new;
                $new = array();
        }

        $input_array = self::merge($input_array, $previous);
    }

    /**
     * Will delete array value by entering path.
     * ['user' => ['address' => 'My Address']]
     *     ----> user/address
     * --
     * @param   string  $path
     * @param   array   $input_array
     * --
     * @return  void
     */
    public static function delete_by_path($path, &$input_array)
    {
        $input_array = self::_delete_by_path_helper($input_array, $path, null);
    }

    /**
     * Delete by path helper
     * --
     * @param   array   $input_array
     * @param   string  $path
     * @param   string  $cp
     * --
     * @return  array
     */
    protected static function _delete_by_path_helper($input_array, $path, $cp)
    {
        $result = array();

        foreach ($input_array as $k => $i) {
            $cup = $cp . '/' . $k;

            if (trim($cup,'/') == trim($path,'/')) { continue; }

            if (is_array($i))
                { $result[$k] = self::_delete_by_path_helper($i, $path, $cup); }
            else
                { $result[$k] = $i; }
        }

        return $result;
    }

    /**
     * Will trim array values(!)
     * --
     * @param   array   $input_array
     * @param   string  $mask
     * --
     * @return  null
     */
    public static function trim(&$input_array, $mask = false)
    {
        if (self::is_empty($input_array)) { return false; }

        foreach ($input_array as $key => $val) {
            if ($mask)
                { $input_array[$key] = trim($val, $mask); }
            else
                { $input_array[$key] = trim($val); }
        }
    }

    /**
     * Get first array's element.
     * --
     * @param  array $array
     * --
     * @return mixed
     */
    public static function first($array)
    {
        return reset($array);
    }

    /**
     * Get last array's element.
     * --
     * @param  array $array
     * --
     * @return mixed
     */
    public static function last($array)
    {
        return end($array);
    }

    /**
     * Get first array's key.
     * --
     * @param  array $array
     * --
     * @return mixed String | Integer
     */
    public static function first_key($array)
    {
        reset($array);

        return key($array);
    }

    /**
     * Get last array's key.
     * --
     * @param  array $array
     * --
     * @return mixed String | Integer
     */
    public static function last_key($array)
    {
        end($array);

        return key($array);
    }

    /**
     * Remove array element, by value.
     * This will remove the first element.
     * --
     * @param  string $needle
     * @param  array $haystack
     * --
     * @return array
     */
    public static function delete_by_value($needle, $haystack)
    {
        if ( ($key = array_search($needle, $haystack)) !== false ) {
            unset($haystack[$key]);
        }
        return $haystack;
    }

    /**
     * Remove array element, bt value.
     * This will remobe all elements with particular value.
     * --
     * @param  string $needle
     * @param  array $haystack
     * --
     * @return array
     */
    public static function delete_by_value_all($needle, $haystack)
    {
        while ( ($key = array_search($needle, $haystack)) !== false ) {
            unset($haystack[$key]);
        }
        return $haystack;
    }
}
