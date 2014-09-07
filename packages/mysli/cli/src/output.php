<?php

namespace mysli\cli {

    \inject::to(__namespace__)
    ->from('mysli/core/type/str')
    ->from('mysli/cli/util', 'cutil');

    class output {
        /**
         * Print line.
         * @param   string  $type available:
         * info    Regular white message
         * error   Red message
         * warn    Yellow message
         * success Green message
         * @param   string  $message
         * @param   array   $args if message contains %s, %d
         * @param   boolean $new_line should message be in new line
         * @return  null
         */
        static function line($type, $message, array $args=[], $new_line=true) {
            switch (str::to_lower($type)) {
                case 'error':
                    $color = "\x1b[31;01m";
                    break;
                case 'warn':
                    $color = "\x1b[33;01m";
                    break;
                case 'success':
                    $color = "\x1b[32;01m";
                    break;
                default:
                    $color = null;
            }
            fwrite(
                STDOUT,
                (!is_null($color) ? $color : '') .
                sprintf($message, $args) .
                "\x1b[39;49;00m");

            if ($new_line) {
                fwrite(STDOUT, PHP_EOL);
            }
            flush();
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
         * @param  boolean $new_line
         */
        static function warn($message, array $args=[], $new_line=true) {
            return self::line('warn', $message, $args, $new_line);
        }
        /**
         * Print error
         * @param  string  $message
         * @param  array   $args
         * @param  boolean $new_line
         */
        static function error($message, array $args=[], $new_line=true) {
            return self::line('error', $message, $args, $new_line);
        }
        /**
         * Print information
         * @param  string  $message
         * @param  array   $args
         * @param  boolean $new_line
         */
        static function info($message, array $args=[], $new_line=true) {
            return self::line('info', $message, $args, $new_line);
        }
        /**
         * Print success
         * @param  string  $message
         * @param  array   $args
         * @param  boolean $new_line
         */
        static function success($message, array $args=[], $new_line=true) {
            return self::line('success', $message, $args, $new_line);
        }
        /**
         * Fill full width of the line with particular character(s).
         * @param  string $character
         */
        static function fill($character) {
            $width = (int) cutil::execute('tput cols');
            if ($width === 0) { return; }
            $width = floor($width / strlen($character));
            self::info(str_repeat($character, $width));
        }
    }
}
