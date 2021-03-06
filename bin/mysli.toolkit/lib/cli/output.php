<?php

namespace mysli\toolkit\cli; class output
{
    const __use = '.cli.util';

    /**
     * For details see: http://misc.flogisoft.com/bash/tip_colors_and_formatting
     * This is a lis of all formats with codes.
     * --
     * @var array
     */
    private static $format =
    [
        // Clean all
        'all'              => [0, 0],

        // Formatting
        'bold'             => [1, 0],
        'dim'              => [2, 0],
        'underline'        => [4, 0],
        'blink'            => [5, 0],
        'invert'           => [7, 0], // invert the foreground and background colors
        'hidden'           => [8, 0],

        // Foreground (text) colors
        'default'          => [39, 39],
        'black'            => [30, 39],
        'red'              => [31, 39],
        'green'            => [32, 39],
        'yellow'           => [33, 39],
        'blue'             => [34, 39],
        'magenta'          => [35, 39],
        'cyan'             => [36, 39],
        'light_gray'       => [37, 39],
        'dark_gray'        => [90, 39],
        'light_red'        => [91, 39],
        'light_green'      => [92, 39],
        'light_yellow'     => [93, 39],
        'light_blue'       => [94, 39],
        'light_magenta'    => [95, 39],
        'light_cyan'       => [96, 39],
        'white'            => [97, 39],

        // Background colors
        'bg_default'       => [49, 49],
        'bg_black'         => [40, 49],
        'bg_red'           => [41, 49],
        'bg_green'         => [42, 49],
        'bg_yellow'        => [43, 49],
        'bg_blue'          => [44, 49],
        'bg_magenta'       => [45, 49],
        'bg_cyan'          => [46, 49],
        'bg_light_gray'    => [47, 49],
        'bg_dark_gray'     => [100, 49],
        'bg_light_red'     => [101, 49],
        'bg_light_green'   => [102, 49],
        'bg_light_yellow'  => [103, 49],
        'bg_light_blue'    => [104, 49],
        'bg_light_magenta' => [105, 49],
        'bg_light_cyan'    => [106, 49],
        'bg_white'         => [107, 49],
    ];

    /**
     * Length of last output line. This is used when aligning text to the right.
     * --
     * @var integer
     */
    private static $last_length = 0;

    /**
     * Print plain line of text.
     * --
     * @param   string  $message
     * @param   boolean $new_line Append a new line at the end.
     */
    static function line($string, $new_line=true)
    {
        echo $string;

        if ($new_line)
        {
            // echo "\e[0m";
            static::$last_length = 0;
            echo PHP_EOL;
        }
        else
        {
            static::$last_length += strlen(
                preg_replace('/\\e\[[0-9]+m/', '', $string)
            );
        }
    }

    /**
     * Position input to the right part of the screen.
     * --
     * @param  string  $message
     * @param  integer $padding
     */
    static function right($message, $padding=2)
    {
        $len = strlen(preg_replace('/\\e\[[0-9]+m/', '', $message));
        $pos = util::terminal_width() - $padding - static::$last_length;
        $pos = $pos - $len;

        if ($pos < 0)
        {
            $pos = 0;
        }

        return str_repeat(' ', $pos) . $message;
    }

    /**
     * Format output.
     * (@see static::$format)
     * --
     * @example
     * Use: <green>GREEN!</green>
     * <right>OK (Tags can be left open.)
     * --
     * @param string $format
     * @param array  $params Use vsprintf to modify output.
     */
    static function format($format, array $params=[])
    {
        $format = preg_replace_callback(
            '/<(\/)?([a-z_]{3,})>/i',
            function ($match)
            {
                if (isset(static::$format[$match[2]]))
                {
                    $f = static::$format[$match[2]][(int) ($match[1] === '/')];
                    return "\e[{$f}m";
                }
                elseif ($match[2] === 'right')
                {
                    return '[[[right/]]]';
                }
            },
            $format
        );

        // Do we have new line character?
        if (substr($format, -1) === "\n")
        {
            $format = substr($format, 0, -1);
            $new_line = true;
        }
        else
        {
            $new_line = false;
        }

        $output = vsprintf($format, $params);

        if (strpos($format, '[[[right/]]]') !== false)
        {
            $output = explode('[[[right/]]]', $output, 2);
            static::line($output[0], false);
            static::line(static::right($output[1]), true);
        }
        else
        {
            static::line($output, $new_line);
        }
    }

    /**
     * Fill full width of the line with particular character(s).
     * --
     * @param string $character
     */
    static function fill($character)
    {
        $width = util::terminal_width() ?: 75;
        $width = floor($width / strlen($character));
        static::line(str_repeat($character, $width));
    }

    /*
    --- Format shortcuts -------------------------------------------------------
     */

    // Basic formatting
    static function bold($s, $n=true)      { static::line("\e[1m{$s}\e[0m", $n); }
    static function dim($s, $n=true)       { static::line("\e[2m{$s}\e[0m", $n); }
    static function underline($s, $n=true) { static::line("\e[4m{$s}\e[0m", $n); }
    static function blink($s, $n=true)     { static::line("\e[5m{$s}\e[0m", $n); }
    static function invert($s, $n=true)    { static::line("\e[7m{$s}\e[0m", $n); }
    static function hidden($s, $n=true)    { static::line("\e[8m{$s}\e[0m", $n); }

    // Foreground (text) colors
    static function black($s, $n=true)         { static::line("\e[30m{$s}\e[39m", $n); }
    static function red($s, $n=true)           { static::line("\e[31m{$s}\e[39m", $n); }
    static function green($s, $n=true)         { static::line("\e[32m{$s}\e[39m", $n); }
    static function yellow($s, $n=true)        { static::line("\e[33m{$s}\e[39m", $n); }
    static function blue($s, $n=true)          { static::line("\e[34m{$s}\e[39m", $n); }
    static function magenta($s, $n=true)       { static::line("\e[35m{$s}\e[39m", $n); }
    static function cyan($s, $n=true)          { static::line("\e[36m{$s}\e[39m", $n); }
    static function light_gray($s, $n=true)    { static::line("\e[37m{$s}\e[39m", $n); }
    static function dark_gray($s, $n=true)     { static::line("\e[90m{$s}\e[39m", $n); }
    static function light_red($s, $n=true)     { static::line("\e[91m{$s}\e[39m", $n); }
    static function light_green($s, $n=true)   { static::line("\e[92m{$s}\e[39m", $n); }
    static function light_yellow($s, $n=true)  { static::line("\e[93m{$s}\e[39m", $n); }
    static function light_blue($s, $n=true)    { static::line("\e[94m{$s}\e[39m", $n); }
    static function light_magenta($s, $n=true) { static::line("\e[95m{$s}\e[39m", $n); }
    static function light_cyan($s, $n=true)    { static::line("\e[96m{$s}\e[39m", $n); }
    static function white($s, $n=true)         { static::line("\e[97m{$s}\e[39m", $n); }

    // Background colors
    static function bg_black($s, $n=true)         { static::line("\e[40m{$s}\e[49m", $n); }
    static function bg_red($s, $n=true)           { static::line("\e[41m{$s}\e[49m", $n); }
    static function bg_green($s, $n=true)         { static::line("\e[42m{$s}\e[49m", $n); }
    static function bg_yellow($s, $n=true)        { static::line("\e[43m{$s}\e[49m", $n); }
    static function bg_blue($s, $n=true)          { static::line("\e[44m{$s}\e[49m", $n); }
    static function bg_magenta($s, $n=true)       { static::line("\e[45m{$s}\e[49m", $n); }
    static function bg_cyan($s, $n=true)          { static::line("\e[46m{$s}\e[49m", $n); }
    static function bg_light_gray($s, $n=true)    { static::line("\e[47m{$s}\e[49m", $n); }
    static function bg_dark_gray($s, $n=true)     { static::line("\e[100m{$s}\e[49m", $n); }
    static function bg_light_red($s, $n=true)     { static::line("\e[101m{$s}\e[49m", $n); }
    static function bg_light_green($s, $n=true)   { static::line("\e[102m{$s}\e[49m", $n); }
    static function bg_light_yellow($s, $n=true)  { static::line("\e[103m{$s}\e[49m", $n); }
    static function bg_light_blue($s, $n=true)    { static::line("\e[104m{$s}\e[49m", $n); }
    static function bg_light_magenta($s, $n=true) { static::line("\e[105m{$s}\e[49m", $n); }
    static function bg_light_cyan($s, $n=true)    { static::line("\e[106m{$s}\e[49m", $n); }
    static function bg_white($s, $n=true)         { static::line("\e[107m{$s}\e[49m", $n); }
}
