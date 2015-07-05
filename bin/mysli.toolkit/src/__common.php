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

/**
 * Standard log as html.
 * --
 * @param array $logs
 * --
 * @return string
 */
function log_to_html($logs)
{
    $css_output = "width:100%;";
    $css_message = "width:100%; background:#234; color:#eee;";

    $output = <<<STYLE
    <style type="text/css">
        section.logs
        {
            width: 100%;
        }
        section.logs div.log-message
        {
            display: block;
            background: #0F181A;
            color: #999;
            border-bottom: 1px solid #444;
            padding: 10px;
            font-family: sans;
            font-size: 12px;
        }
        section.logs div.log-message span.message
        {
            font-family: monospace;
            font-size: 16px;
            display: block;
            margin-bottom: 10px;
        }
        section.logs div.log-message.type-debug span.message
        {
            color: #6e973d;
        }
        section.logs div.log-message.type-info span.message
        {
            color: #aee87b;
        }
        section.logs div.log-message.type-notice span.message
        {
            color: #ddb691;
        }
        section.logs div.log-message.type-warning span.message
        {
            color: #ed683b;
        }
        section.logs div.log-message.type-error span.message
        {
            color: #f23d3d;
        }
        section.logs div.log-message.type-panic span.message
        {
            color: #fcc;
            background: #893434;
            padding: 5px;
            border-radius: 4px;
        }
        section.logs div.log-message.type-panic span.message:before
        {
            content: "PANIC!!";
            font-weight: bold;
            font-size: 18px;
            display: block;
        }
        section.logs div.log-message span.type
        {
            display: none;
        }
        section.logs div.log-message span.from
        {
            padding-right: 10px;
        }
    </style>
STYLE;

    $output .= '<section class="logs" />';

    foreach ($logs as $k => $log)
    {
        $output .= '<div class="log-message type-'.$log['type'].'">';
        $output .= '<span class="type">'.$log['type'].'</span>';
        $output .= '<span class="message">'.$log['message'].'</span>';
        $output .= '<span class="from">'.$log['from'].'</span>';
        $output .= '<span class="time">'.$log['timestamp'].'</span>';
        $output .= '</div>';
    }

    $output .= '</section>';

    return $output;
}
