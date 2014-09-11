<?php

/**
 * Determine if this is command line interface.
 * @return boolean
 */
function is_cli() {
    return php_sapi_name() === 'cli' || defined('STDIN');
}

/**
 * Output variable as: <pre>print_r($variable)</pre> (this is only for debuging)
 * This will die after dumpign variables on screen.
 */
function dump() {
    die(call_user_func_array('dump_rr', func_get_args()));
}

/**
 * Dump, but don't die - echo results instead.
 * @return null
 */
function dump_r() {
    echo call_user_func_array('dump_rr', func_get_args());
}

/**
 * Dump, but don't die - return results instead.
 * @return string
 */
function dump_rr() {
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
 * Inject shortcut.
 * @param  string $namespace
 * @param  mixed  ...        parameters to inject, string or array.
 * @return null
 */
function __use($namespace) {
    // DO SOMETHING :)
}
