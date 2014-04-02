<?php

/**
 * Determine if this is command line interface.
 * --
 * @return boolean
 */
function is_cli() { return php_sapi_name() === 'cli' || defined('STDIN'); }

/**
 * Retrun data path.
 * --
 * @param string ... Accept multiple parameters, to build full path from them.
 * --
 * @return string
 */
function datpath()
{
    $arguments = func_get_args();
    $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
    return ds(MYSLI_DATPATH, $arguments);
}

/**
 * Retrun packages path.
 * --
 * @param string ... Accept multiple parameters, to build full path from them.
 * --
 * @return string
 */
function pkgpath()
{
    $arguments = func_get_args();
    $arguments = implode(DIRECTORY_SEPARATOR, $arguments);
    return ds(MYSLI_PKGPATH, $arguments);
}

/**
 * Correct Directory Separators.
 * --
 * @param string ... Accept multiple parameters, to build full path from them.
 * --
 * @return string
 */
function ds()
{
    $path = func_get_args();
    $path = implode(DIRECTORY_SEPARATOR, $path);

    if ($path) {
        return preg_replace('/[\/\\\\]+/', DIRECTORY_SEPARATOR, $path);
    }
    else {
        return null;
    }
}

/**
 * Method to calculate the relative path from $from to $to.
 * Note: On Windows it does not work when $from and $to are on different drives.
 * Credit: http://www.php.net/manual/en/function.realpath.php#105876
 * --
 * @param  string $to
 * @param  string $from
 * @param  string $ps
 * --
 * @return string
 */
function relative_path($to, $from, $ps = DIRECTORY_SEPARATOR)
{
    $ar_from = explode($ps, rtrim($from, $ps));
    $ar_to = explode($ps, rtrim($to, $ps));
    while(count($ar_from) && count($ar_to) && ($ar_from[0] == $ar_to[0]))
    {
        array_shift($ar_from);
        array_shift($ar_to);
    }
    return str_pad('', count($ar_from) * 3, '..' . $ps) . implode($ps, $ar_to);
}

/**
 * Output variable as: <pre>print_r($variable)</pre> (this is only for debuging)
 * This will die after dumpign variables on screen.
 */
function dump()
{
    die(call_user_func_array('dump_r', func_get_args()));
}

/**
 * Dump, but don't die - return results instead.
 * --
 * @return string
 */
function dump_r()
{
    $arguments = func_get_args();
    $result = '';

    foreach ($arguments as $variable)
    {
        if (is_bool($variable)) {
            $bool = $variable ? 'true' : 'false';
        }
        else {
            $bool = false;
        }

        $result .= (!is_cli()) ? "\n<pre>\n" : "\n";
        $result .= '' . gettype($variable);
        $result .= (is_string($variable) ? '['.strlen($variable).']' : '');
        $result .=  ': ' . (is_bool($variable) ? $bool : print_r($variable, true));
        $result .= (!is_cli()) ? "\n</pre>\n" : "\n";
    }

    return $result;
}

/**
 * This function is part of the array_column library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2013 Ben Ramsey <http://benramsey.com>
 * @license http://opensource.org/licenses/MIT MIT
 */
if (!function_exists('array_column')) {

    /**
     * Returns the values from a single column of the input array, identified by
     * the $columnKey.
     *
     * Optionally, you may provide an $indexKey to index the values in the returned
     * array by the values from the $indexKey column in the input array.
     *
     * @param array $input A multi-dimensional array (record set) from which to pull
     *                     a column of values.
     * @param mixed $columnKey The column of values to return. This value may be the
     *                         integer key of the column you wish to retrieve, or it
     *                         may be the string key name for an associative array.
     * @param mixed $indexKey (Optional.) The column to use as the index/keys for
     *                        the returned array. This value may be the integer key
     *                        of the column, or it may be the string key name.
     * @return array
     */
    function array_column($input = null, $columnKey = null, $indexKey = null)
    {
        // Using func_get_args() in order to check for proper number of
        // parameters and trigger errors exactly as the built-in array_column()
        // does in PHP 5.5.
        $argc = func_num_args();
        $params = func_get_args();

        if ($argc < 2) {
            trigger_error("array_column() expects at least 2 parameters, {$argc} given", E_USER_WARNING);
            return null;
        }

        if (!is_array($params[0])) {
            trigger_error('array_column() expects parameter 1 to be array, ' . gettype($params[0]) . ' given', E_USER_WARNING);
            return null;
        }

        if (!is_int($params[1])
            && !is_float($params[1])
            && !is_string($params[1])
            && $params[1] !== null
            && !(is_object($params[1]) && method_exists($params[1], '__toString'))
        ) {
            trigger_error('array_column(): The column key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        if (isset($params[2])
            && !is_int($params[2])
            && !is_float($params[2])
            && !is_string($params[2])
            && !(is_object($params[2]) && method_exists($params[2], '__toString'))
        ) {
            trigger_error('array_column(): The index key should be either a string or an integer', E_USER_WARNING);
            return false;
        }

        $paramsInput = $params[0];
        $paramsColumnKey = ($params[1] !== null) ? (string) $params[1] : null;

        $paramsIndexKey = null;
        if (isset($params[2])) {
            if (is_float($params[2]) || is_int($params[2])) {
                $paramsIndexKey = (int) $params[2];
            } else {
                $paramsIndexKey = (string) $params[2];
            }
        }

        $resultArray = array();

        foreach ($paramsInput as $row) {

            $key = $value = null;
            $keySet = $valueSet = false;

            if ($paramsIndexKey !== null && array_key_exists($paramsIndexKey, $row)) {
                $keySet = true;
                $key = (string) $row[$paramsIndexKey];
            }

            if ($paramsColumnKey === null) {
                $valueSet = true;
                $value = $row;
            } elseif (is_array($row) && array_key_exists($paramsColumnKey, $row)) {
                $valueSet = true;
                $value = $row[$paramsColumnKey];
            }

            if ($valueSet) {
                if ($keySet) {
                    $resultArray[$key] = $value;
                } else {
                    $resultArray[] = $value;
                }
            }

        }

        return $resultArray;
    }

}
