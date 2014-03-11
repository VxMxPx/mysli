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
