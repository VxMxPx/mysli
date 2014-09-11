<?php

namespace mysli\cli {

    \inject::to(__namespace__)
    ->from('mysli/core/type/str')
    ->from('mysli/cli/util', 'cutil');

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
                        $f = self::$format[$match[2]][(int) $match[1] == '-'];
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
         * Print warning
         * @param  string  $message
         * @param  array   $args
         */
        static function warn($message, array $args=[]) {
            return self::line(
                self::format("+yellow{$message}-all", $args), true);
        }
        /**
         * Print error
         * @param  string  $message
         * @param  array   $args
         */
        static function error($message, array $args=[]) {
            return self::line(
                self::format("+red{$message}-all", $args), true);
        }
        /**
         * Print information
         * @param  string  $message
         * @param  array   $args
         * @param  boolean $new_line
         */
        static function info($message, array $args=[]) {
            return self::line(
                self::format("-all{$message}", $args), true);
        }
        /**
         * Print success
         * @param  string  $message
         * @param  array   $args
         * @param  boolean $new_line
         */
        static function success($message, array $args=[]) {
            return self::line(
                self::format("+green{$message}-all", $args), true);
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
    }
}
