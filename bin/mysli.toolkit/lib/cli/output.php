<?php

/**
 * # Output
 *
 * Used to output text (in various styles) to the console.
 *
 * ## Usage
 *
 * To print a single line of a regular text, `line` method can be used:
 *
 *     output::line('Hello world!');
 *
 * To fill full width of terminal window with a particular character `fill` method
 * is available:
 *
 *     output::fill('-');
 *
 * To format a string (change text color, background color,...), there's a `format`
 * method:
 *
 *     output::format("Today is a <bold>%s</bold> day!", ['nice']);
 *
 *
 * Tags need not to be closed. To close all opened tags </all> can be used.
 *
 * Available tags are:
 *
 * Formating: bold, dim, underline, blink, invert, hidden
 *
 * Text color: default, black, red, green, yellow, blue, magenta, cyan, light_gray,
 * dark_gray, light_red, light_green, light_yellow, light_blue, light_magenta,
 * light_cyan, white
 *
 * Background color: bg_default, bg_black, bg_red, bg_green, bg_yellow, bg_blue,
 * bg_magenta, bg_cyan, bg_light_gray, bg_dark_gray, bg_light_red, bg_light_green,
 * bg_light_yellow, bg_light_blue, bg_light_magenta, bg_light_cyan, bg_white
 *
 * There are shortcut methods available for each tag:
 *
 *     output::red('Red text!');
 *     output::green('Green text!');
 *
 * Please use `ui` class for semantic output, e.g. to output text by particular
 * role, as: title, list, error, ...
 *
 */
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
        'bold'             => [1, 21],
        'dim'              => [2, 22],
        'underline'        => [4, 24],
        'blink'            => [5, 25],
        'invert'           => [7, 27], // invert the foreground and background colors
        'hidden'           => [8, 28],

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
        fwrite(STDOUT, $string);

        if ($new_line)
        {
            fwrite(STDOUT, "\e[0m");
            self::$last_length = 0;
            fwrite(STDOUT, PHP_EOL);
        }
        else
        {
            self::$last_length += strlen(
                preg_replace('/\\e\[[0-9]+m/', '', $string)
            );
        }

        flush();
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
        $pos = util::terminal_width() - $padding - self::$last_length;
        $pos = $pos - $len;

        if ($pos < 0)
        {
            $pos = 0;
        }

        return str_repeat(' ', $pos) . $message;
    }

    /**
     * Format output,
     * --
     * @see self::$format
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
                if (isset(self::$format[$match[2]]))
                {
                    $f = self::$format[$match[2]][(int) ($match[1] === '/')];
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
            self::line($output[0], false);
            self::line(self::right($output[1]), $new_line);
        }
        else
        {
            self::line($output, $new_line);
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
        self::line(str_repeat($character, $width));
    }

    /*
    --- Format shortcuts -------------------------------------------------------
     */

    // Basic formatting
    static function bold($s, $n=true)      { self::line("\e[1m{$s}\e[21m", $n); }
    static function dim($s, $n=true)       { self::line("\e[2m{$s}\e[22m", $n); }
    static function underline($s, $n=true) { self::line("\e[4m{$s}\e[24m", $n); }
    static function blink($s, $n=true)     { self::line("\e[5m{$s}\e[25m", $n); }
    static function invert($s, $n=true)    { self::line("\e[7m{$s}\e[27m", $n); }
    static function hidden($s, $n=true)    { self::line("\e[8m{$s}\e[28m", $n); }

    // Foreground (text) colors
    static function black($s, $n=true)         { self::line("\e[30m{$s}\e[39m", $n); }
    static function red($s, $n=true)           { self::line("\e[31m{$s}\e[39m", $n); }
    static function green($s, $n=true)         { self::line("\e[32m{$s}\e[39m", $n); }
    static function yellow($s, $n=true)        { self::line("\e[33m{$s}\e[39m", $n); }
    static function blue($s, $n=true)          { self::line("\e[34m{$s}\e[39m", $n); }
    static function magenta($s, $n=true)       { self::line("\e[35m{$s}\e[39m", $n); }
    static function cyan($s, $n=true)          { self::line("\e[36m{$s}\e[39m", $n); }
    static function light_gray($s, $n=true)    { self::line("\e[37m{$s}\e[39m", $n); }
    static function dark_gray($s, $n=true)     { self::line("\e[90m{$s}\e[39m", $n); }
    static function light_red($s, $n=true)     { self::line("\e[91m{$s}\e[39m", $n); }
    static function light_green($s, $n=true)   { self::line("\e[92m{$s}\e[39m", $n); }
    static function light_yellow($s, $n=true)  { self::line("\e[93m{$s}\e[39m", $n); }
    static function light_blue($s, $n=true)    { self::line("\e[94m{$s}\e[39m", $n); }
    static function light_magenta($s, $n=true) { self::line("\e[95m{$s}\e[39m", $n); }
    static function light_cyan($s, $n=true)    { self::line("\e[96m{$s}\e[39m", $n); }
    static function white($s, $n=true)         { self::line("\e[97m{$s}\e[39m", $n); }

    // Background colors
    static function bg_black($s, $n=true)         { self::line("\e[40m{$s}\e[49m", $n); }
    static function bg_red($s, $n=true)           { self::line("\e[41m{$s}\e[49m", $n); }
    static function bg_green($s, $n=true)         { self::line("\e[42m{$s}\e[49m", $n); }
    static function bg_yellow($s, $n=true)        { self::line("\e[43m{$s}\e[49m", $n); }
    static function bg_blue($s, $n=true)          { self::line("\e[44m{$s}\e[49m", $n); }
    static function bg_magenta($s, $n=true)       { self::line("\e[45m{$s}\e[49m", $n); }
    static function bg_cyan($s, $n=true)          { self::line("\e[46m{$s}\e[49m", $n); }
    static function bg_light_gray($s, $n=true)    { self::line("\e[47m{$s}\e[49m", $n); }
    static function bg_dark_gray($s, $n=true)     { self::line("\e[100m{$s}\e[49m", $n); }
    static function bg_light_red($s, $n=true)     { self::line("\e[101m{$s}\e[49m", $n); }
    static function bg_light_green($s, $n=true)   { self::line("\e[102m{$s}\e[49m", $n); }
    static function bg_light_yellow($s, $n=true)  { self::line("\e[103m{$s}\e[49m", $n); }
    static function bg_light_blue($s, $n=true)    { self::line("\e[104m{$s}\e[49m", $n); }
    static function bg_light_magenta($s, $n=true) { self::line("\e[105m{$s}\e[49m", $n); }
    static function bg_light_cyan($s, $n=true)    { self::line("\e[106m{$s}\e[49m", $n); }
    static function bg_white($s, $n=true)         { self::line("\e[107m{$s}\e[49m", $n); }
}
