<?php

namespace mysli\framework\cli {

    __use(__namespace__,
        '../type/str',
        ['./util' => 'cutil']
    );

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

        /**
         * Print line.
         * @param   string  $message
         * @param   boolean $new_line append new line after this one
         * @return  null
         */
        static function line($string, $new_line=true) {
            fwrite(STDOUT, $message);
            if ($new_line) {
                fwrite(STDOUT, PHP_EOL);
            }
            flush();
        }
        /**
         * Print warning
         * @param  string  $message
         */
        static function warn($message) {
            self::line(self::yellow($message), true);
        }
        /**
         * Print error
         * @param  string  $message
         */
        static function error($message) {
            self::line(self::red($message), true);
        }
        /**
         * Print information
         * @param  string  $message
         */
        static function info($message) {
            self::line($message, true);
        }
        /**
         * Print success
         * @param  string  $message
         */
        static function success($message) {
            self::line(self::green($message), true);
        }
        /**
         * Format output, to open tag use plus(+), to close use minus(-) e.g.:
         * +bold+red %s -bold-red
         * see self::$format for list of available formats, addional to that:
         * to close all use: -all, e.g.: +bold+red+bg_blue %s -all
         * @param  string $format
         * @param  array $params
         * @return string
         */
        static function format($format, array $params=[]) {
            $format = preg_replace_callback(
                '/([\+\-])([a-z_]{3,})/i', function ($match) {
                    if (isset(self::$format[$match[2]])) {
                        $f = self::$format[$match[2]][(int) ($match[1] == '-')];
                        return "\\e[{$f}m";
                    }
                }, $format);
            return vsprintf($format, $params);
        }
        /**
         * Create a new line(s)
         * @param integer $num Nnumber of new lines
         */
        static function nl($num=1) {
            fwrite(STDOUT, str_repeat(PHP_EOL, (int) $num));
        }
        /**
         * Fill full width of the line with particular character(s).
         * @param  string $character
         */
        static function fill($character) {
            $width = cutil::terminal_width() ?: 75;
            $width = floor($width / strlen($character));
            self::info(str_repeat($character, $width));
        }

        // format shortcuts

        // basic formatting
        static function bold($s)      { return "\e[1m{$s}\e[21m"; }
        static function dim($s)       { return "\e[2m{$s}\e[22m"; }
        static function underline($s) { return "\e[4m{$s}\e[24m"; }
        static function blink($s)     { return "\e[5m{$s}\e[25m"; }
        static function invert($s)    { return "\e[7m{$s}\e[27m"; }
        static function hidden($s)    { return "\e[8m{$s}\e[28m"; }
        // foreground (text) colors
        static function black($s)         { return "\e[30m{$s}\e[39m"; }
        static function red($s)           { return "\e[31m{$s}\e[39m"; }
        static function green($s)         { return "\e[32m{$s}\e[39m"; }
        static function yellow($s)        { return "\e[33m{$s}\e[39m"; }
        static function blue($s)          { return "\e[34m{$s}\e[39m"; }
        static function magenta($s)       { return "\e[35m{$s}\e[39m"; }
        static function cyan($s)          { return "\e[36m{$s}\e[39m"; }
        static function light_gray($s)    { return "\e[37m{$s}\e[39m"; }
        static function dark_gray($s)     { return "\e[90m{$s}\e[39m"; }
        static function light_red($s)     { return "\e[91m{$s}\e[39m"; }
        static function light_green($s)   { return "\e[92m{$s}\e[39m"; }
        static function light_yellow($s)  { return "\e[93m{$s}\e[39m"; }
        static function light_blue($s)    { return "\e[94m{$s}\e[39m"; }
        static function light_magenta($s) { return "\e[95m{$s}\e[39m"; }
        static function light_cyan($s)    { return "\e[96m{$s}\e[39m"; }
        static function white($s)         { return "\e[97m{$s}\e[39m"; }
        // background colors
        static function bg_black($s)         { return "\e[40m{$s}\e[49m"; }
        static function bg_red($s)           { return "\e[41m{$s}\e[49m"; }
        static function bg_green($s)         { return "\e[42m{$s}\e[49m"; }
        static function bg_yellow($s)        { return "\e[43m{$s}\e[49m"; }
        static function bg_blue($s)          { return "\e[44m{$s}\e[49m"; }
        static function bg_magenta($s)       { return "\e[45m{$s}\e[49m"; }
        static function bg_cyan($s)          { return "\e[46m{$s}\e[49m"; }
        static function bg_light_gray($s)    { return "\e[47m{$s}\e[49m"; }
        static function bg_dark_gray($s)     { return "\e[100m{$s}\e[49m"; }
        static function bg_light_red($s)     { return "\e[101m{$s}\e[49m"; }
        static function bg_light_green($s)   { return "\e[102m{$s}\e[49m"; }
        static function bg_light_yellow($s)  { return "\e[103m{$s}\e[49m"; }
        static function bg_light_blue($s)    { return "\e[104m{$s}\e[49m"; }
        static function bg_light_magenta($s) { return "\e[105m{$s}\e[49m"; }
        static function bg_light_cyan($s)    { return "\e[106m{$s}\e[49m"; }
        static function bg_white($s)         { return "\e[107m{$s}\e[49m"; }
    }
}