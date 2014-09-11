<?php

namespace mysli\framework\cli {

    __use(__namespace__,
        '../type/str'
    );

    class input {
        /**
         * Capture the cursor, return user input or function's result.
         * @param  string   $title the text displayed for input question
         * @param  function $call will be execute for each input, this will run,
         * until function ($call) is returning null
         * @return mixed
         */
        static function line($title, $call=null) {
            // loop if callback is defined
            if ($call) {
                $result = null;
                do {
                    $stdin = self::line($title);
                    $result = $func($stdin);
                } while($result === null);
                return $result;
            }
            // simple input, no callback
            if (function_exists('readline')) {
                $stdin = readline($title);
                readline_add_history($stdin);
            }
            else {
                fwrite(STDOUT, $title);
                $stdin = fread(STDIN, 8192);
            }
            return $stdin;
        }
        /**
         * Capture the cursos, wait for user's input.
         * Exit on double enter and function ($call) return null.
         * @param  string   $title the text displayed for input question
         * @param  function $call will be execute for each input, this will run,
         * until function ($call) is returning null
         * @return mixed
         */
        static function multiline($title, $call) {
            $result = null;
            $stdin  = '';
            $enter_key = 0;

            do {
                do {
                    $stdin_t = self::line($title);
                    $stdin .= $stdin_t . "\n";
                    if ($stdin_t === '') {
                        $enter_key++;
                    } else {
                        if ($enter_key > 0) {
                            $enter_key--;
                        }
                    }
                } while ($enter_key < 1);
                $result = $call(trim($stdin));
            } while($result === null);

            return $result;
        }
        /**
         * Capture the cursor, return user input or function's result.
         * User's input will be hidden.
         * @param  string   $title the text displayed for input question
         * @param  function $call will be execute for each input, this will run,
         * until function ($call) is returning null
         * @return mixed
         */
        static function password($title, $call=null) {
            $result = null;
            `stty -echo`;
            $result = self::line($title, $call);
            `stty echo`;
            return $result;
        }
        /**
         * Ask user a question to which Y/n is the only possible answer.
         * @param  string  $text
         * @param  boolean $default
         * @return boolean
         */
        public static function confirm($text, $default=true)
        {
            $question = $text . ' [' . ($default ? 'Y/n' : 'y/N') . '] ';
            return self::line(
                $question,
                function ($input) use ($default) {
                    $input = str::to_lower($input);
                    if (empty($input)) {
                        return $default;
                    }
                    if ($input === 'y') {
                        return true;
                    }
                    if ($input === 'n') {
                        return false;
                    }
                });
        }
    }
}
