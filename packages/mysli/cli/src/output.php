<?php

namespace mysli\cli {

    use \mysli\base\str as str;
    use \mysli\cli\util as cutil;

    class output {
        /**
         * Print line.
         * @param   string  $type
         *   info    -- Regular white message
         *   error   -- Red message
         *   warn    -- Yellow message
         *   success -- Green message
         * @param   string  $message
         * @param   boolean $new_line should message be in new line
         * @return  null
         */
        static function line($type, $message, $new_line=true) {
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
                (!is_null($color) ? $color : '') . $message . "\x1b[39;49;00m"
            );
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
         * @param  boolean $new_line
         */
        static function warn($message, $new_line=true) {
            return self::out('warn', $message, $new_line);
        }
        /**
         * Print error
         * @param  string  $message
         * @param  boolean $new_line
         */
        static function error($message, $new_line=true) {
            return self::out('error', $message, $new_line);
        }
        /**
         * Print information
         * @param  string  $message
         * @param  boolean $new_line
         */
        static function info($message, $new_line=true) {
            return self::out('info', $message, $new_line);
        }
        /**
         * Print success
         * @param  string  $message
         * @param  boolean $new_line
         */
        static function success($message, $new_line=true) {
            return self::out('success', $message, $new_line);
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
