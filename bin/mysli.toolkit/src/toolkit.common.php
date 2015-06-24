<?php

/**
 * Determine if this is command line interface.
 * --
 * @return boolean
 */
function is_cli()
{
    return php_sapi_name() === 'cli' || defined('STDIN');
}

/**
 * Output variable as: <pre>print_r($variable)</pre> (this is only for debuging)
 * This will die after dumpign variables on screen.
 */
function dump()
{
    die(call_user_func_array('dump_rr', func_get_args()));
}

/**
 * Dump, but don't die - echo results instead.
 * --
 * @return null
 */
function dump_r()
{
    echo call_user_func_array('dump_rr', func_get_args());
}

/**
 * Dump, but don't die - return results instead.
 * --
 * @return string
 */
function dump_rr()
{
    $arguments = func_get_args();
    $result = '';

    foreach ($arguments as $variable)
    {
        if (is_bool($variable))
        {
            $bool = $variable ? 'true' : 'false';
        }
        else
        {
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
 * Format generic exception message when processing multiple lines input.
 * --
 * @param  array   $lines
 * @param  integer $current
 * @param  string  $message
 * @param  string  $file
 * --
 * @return string
 */
function f_error(array $lines, $current, $message, $file=null)
{
    return
        $message . "\n" .
        err_lines($lines, $current, 3) .
        ($file ? "File: `{$file}`\n" : "\n");
}

/**
 * Return -$padding, $current, +$padding lines for exceptions.
 * --
 * @example
 *   11. ::if true
 * >>12.     {username|non_existant_function}
 *   13. ::/if
 * --
 * @param array   $lines
 * @param integer $current
 * @param integer $padding
 * --
 * @return string
 */
function err_lines($lines, $current, $padding=3)
{
    $start    = $current - $padding;
    $end      = $current + $padding;
    $result   = '';

    for ($position = $start; $position <= $end; $position++)
    {
        if (isset($lines[$position]))
        {
            if ($position === $current)
            {
                $result .= ">>";
            }
            else
            {
                $result .= "  ";
            }

            $result .= ($position+1).". {$lines[$position]}\n";
        }
    }

    return $result;
}
