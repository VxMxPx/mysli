<?php

namespace mysli\framework\cli;

__use(__namespace__, '
    mysli.framework.type/str
    ./util  AS  cutil
');

class output {

    // http://misc.flogisoft.com/bash/tip_colors_and_formatting
    private static $format = [
        // clean all
        'all'       => [0, 0],
        // formatting
        'bold'      => [1, 21],
        'dim'       => [2, 22],
        'underline' => [4, 24],
        'blink'     => [5, 25],
        'invert'    => [7, 27], // invert the foreground and background colors
        'hidden'    => [8, 28],
        // foreground (text) colors
        'default'       => [39, 39],
        'black'         => [30, 39],
        'red'           => [31, 39],
        'green'         => [32, 39],
        'yellow'        => [33, 39],
        'blue'          => [34, 39],
        'magenta'       => [35, 39],
        'cyan'          => [36, 39],
        'light_gray'    => [37, 39],
        'dark_gray'     => [90, 39],
        'light_red'     => [91, 39],
        'light_green'   => [92, 39],
        'light_yellow'  => [93, 39],
        'light_blue'    => [94, 39],
        'light_magenta' => [95, 39],
        'light_cyan'    => [96, 39],
        'white'         => [97, 39],
        // background colors
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
    private static $last_length = 0;

    /**
     * Print line.
     * @param   string  $message
     * @param   boolean $new_line append new line after this one
     * @return  null
     */
    static function line($string, $new_line=true) {
        fwrite(STDOUT, $string);
        if ($new_line) {
            fwrite(STDOUT, "\e[0m");
            self::$last_length = 0;
            fwrite(STDOUT, PHP_EOL);
        } else {
            self::$last_length += strlen(preg_replace(
                                            '/\\e\[[0-9]+m/', '', $string));
        }
        flush();
    }
    /**
     * Position input to the right part of the screen.
     * @param  string  $message
     * @param  integer $padding
     * @return null
     */
    static function right($message, $padding=2) {
        $len = strlen(preg_replace('/\\e\[[0-9]+m/', '', $message));
        $pos = cutil::terminal_width() - $padding - self::$last_length;
        $pos = $pos - $len;
        if ($pos < 0) {
            $pos = 0;
        }
        return str_repeat(' ', $pos) . $message;
    }
    /**
     * Format output, to open tag use plus(+), to close use minus(-) e.g.:
     * +bold+red %s -bold-red
     * see self::$format for list of available formats, addional to that:
     * to close all use: -all, e.g.: +bold+red+bg_blue %s -all
     * @param  string $format
     * @param  array $params
     * @return null
     */
    static function format($format, array $params=[]) {
        $format = preg_replace_callback(
            '/([\+\-])([a-z_]{3,})\ ?/i', function ($match) {
                if (isset(self::$format[$match[2]])) {
                    $f = self::$format[$match[2]][(int) ($match[1] == '-')];
                    return "\e[{$f}m";
                } elseif ($match[2] === 'right') {
                    return '+right';
                }
            }, $format);
        $output = vsprintf($format, $params);
        if (strpos($format, '+right') !== false) {
            $output = explode('+right', $output, 2);
            self::line($output[0], false);
            self::line(self::right($output[1]), true);
        } else {
            self::line($output, true);
        }
    }
    /**
     * Create a new line(s)
     * @param integer $num Nnumber of new lines
     */
    static function nl($num=1) {
        self::$last_length = 0;
        fwrite(STDOUT, str_repeat(PHP_EOL, (int) $num));
    }
    /**
     * Fill full width of the line with particular character(s).
     * @param  string $character
     */
    static function fill($character) {
        $width = cutil::terminal_width() ?: 75;
        $width = floor($width / strlen($character));
        self::line(str_repeat($character, $width));
    }

    // format shortcuts
    static function info($s)           { self::line($s); }
    static function warn($s)           { self::yellow($s); }
    static function error($s)          { self::red($s); }
    static function success($s)        { self::green($s); }
    // basic formatting
    static function bold($s)           { self::line("\e[1m{$s}\e[21m"); }
    static function dim($s)            { self::line("\e[2m{$s}\e[22m"); }
    static function underline($s)      { self::line("\e[4m{$s}\e[24m"); }
    static function blink($s)          { self::line("\e[5m{$s}\e[25m"); }
    static function invert($s)         { self::line("\e[7m{$s}\e[27m"); }
    static function hidden($s)         { self::line("\e[8m{$s}\e[28m"); }
    // foreground (text) colors
    static function black($s)          { self::line("\e[30m{$s}\e[39m"); }
    static function red($s)            { self::line("\e[31m{$s}\e[39m"); }
    static function green($s)          { self::line("\e[32m{$s}\e[39m"); }
    static function yellow($s)         { self::line("\e[33m{$s}\e[39m"); }
    static function blue($s)           { self::line("\e[34m{$s}\e[39m"); }
    static function magenta($s)        { self::line("\e[35m{$s}\e[39m"); }
    static function cyan($s)           { self::line("\e[36m{$s}\e[39m"); }
    static function light_gray($s)     { self::line("\e[37m{$s}\e[39m"); }
    static function dark_gray($s)      { self::line("\e[90m{$s}\e[39m"); }
    static function light_red($s)      { self::line("\e[91m{$s}\e[39m"); }
    static function light_green($s)    { self::line("\e[92m{$s}\e[39m"); }
    static function light_yellow($s)   { self::line("\e[93m{$s}\e[39m"); }
    static function light_blue($s)     { self::line("\e[94m{$s}\e[39m"); }
    static function light_magenta($s)  { self::line("\e[95m{$s}\e[39m"); }
    static function light_cyan($s)     { self::line("\e[96m{$s}\e[39m"); }
    static function white($s)          { self::line("\e[97m{$s}\e[39m"); }
    // background colors
    static function bg_black($s)        { self::line("\e[40m{$s}\e[49m"); }
    static function bg_red($s)          { self::line("\e[41m{$s}\e[49m"); }
    static function bg_green($s)        { self::line("\e[42m{$s}\e[49m"); }
    static function bg_yellow($s)       { self::line("\e[43m{$s}\e[49m"); }
    static function bg_blue($s)         { self::line("\e[44m{$s}\e[49m"); }
    static function bg_magenta($s)      { self::line("\e[45m{$s}\e[49m"); }
    static function bg_cyan($s)         { self::line("\e[46m{$s}\e[49m"); }
    static function bg_light_gray($s)   { self::line("\e[47m{$s}\e[49m"); }
    static function bg_dark_gray($s)    { self::line("\e[100m{$s}\e[49m"); }
    static function bg_light_red($s)    { self::line("\e[101m{$s}\e[49m"); }
    static function bg_light_green($s)  { self::line("\e[102m{$s}\e[49m"); }
    static function bg_light_yellow($s) { self::line("\e[103m{$s}\e[49m"); }
    static function bg_light_blue($s)   { self::line("\e[104m{$s}\e[49m"); }
    static function bg_light_magenta($s){ self::line("\e[105m{$s}\e[49m"); }
    static function bg_light_cyan($s)   { self::line("\e[106m{$s}\e[49m"); }
    static function bg_white($s)        { self::line("\e[107m{$s}\e[49m"); }
}
