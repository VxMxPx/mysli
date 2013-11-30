<?php

class MysliVoidMagicObject {
    public function __call($name, $arguments) { return null; }
    public static function __callStatic($name, $arguments) { return null; }
    public function __get($name) { return null; }
    public function __set($name, $value) { return null; }
    public function __isset($name) { return null; }
    public function __unset($name) { return null; }
}

/**
 * Determine if this is command line interface.
 * --
 * @return boolean
 */
function is_cli() { return php_sapi_name() === 'cli' || defined('STDIN'); }

/**
 * Path helper methods.
 * Return full absolute this, library, public and database path.
 * --
 * @param  string $path Leave empty to get only base path.
 * --
 * @return string
 */
function libpath($path = null)
{
    return with(core(), '1801:Core is not instantiated. Cannot use \'libpath\'.')
        ->libpath($path);
}
function pubpath($path = null)
{
    return with(core(), '1802:Core is not instantiated. Cannot use \'pubpath\'.')
        ->pubpath($path);
}
function datpath($path = null)
{
    return with(core(), '1803:Core is not instantiated. Cannot use \'datpath\'.')
        ->datpath($path);
}

/**
 * With particular library .. do something.
 * --
 * @param  mixed $what      object|string:library|string:class
 * @param  mixed $exception Throw exception if $what was not found. False or
 *                          string: Exception message.
 * --
 * @return mixed
 */
function with($what, $exception = false)
{
    if (!$what) {
        if ($exception) {
            throw new Exception($exception, 1800);
        } else {
            return new MysliVoidMagicObject();
        }
    }
    if (is_object($what)) {
        return $what;
    }
    if (is_string($what)) {
        $lib = core('librarian');
        if (!is_object($lib)) {
            if ($exception) {
                throw new Exception($exception, 1800);
            } else {
                return new MysliVoidMagicObject();
            }
        }
        if (strpos($what, '\\') !== false) {
            $what = $lib->ns_to_lib($what);
        }
        return $lib->factory($what);
    }
}

/**
 * Return core instance or one of the core libraries.
 * --
 * @param  string $library
 * --
 * @return mixed  Object | false
 */
function core($library = null)
{
    $core = \Mysli\Core::instance();

    if (!$library) {
        return $core;
    } else {
        if (!is_object($core)) {
            return false;
        }
        if (!property_exists($core, $library)) {
            return false;
        }

        return $core->{$library};
    }
}

/**
 * Correct Directory Separators.
 * --
 * @param string ... Accept multiple parameters, to build full path from them
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
