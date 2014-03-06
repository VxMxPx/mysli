<?php

namespace Mysli\Dot;

class Util
{

    /**
     * Capture the cursor, return user's input.
     * --
     * @param  string $title
     * --
     * @return string
     */
    public static function simple_input($title)
    {
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
     * Capture the cursos - wait for user's input. You must pass in a function,
     * this will run until function is returning null.
     * --
     * @param  string   $title The text displayed for input question.
     * @param  function $func  Function to be executed for each input,
     *                         until function is returning null this will run
     *                         in loop.
     * --
     * @return mixed    Value of the function return
     */
    public static function input($title, $func)
    {
        $result = null;

        do {
            $stdin = self::simple_input($title);
            $result = $func($stdin);
        } while($result === null);

        return $result;
    }

    /**
     * Capture the cursos - wait for user's input. You must pass in a function,
     * this will run until function is returning null.
     * This will exit on double enter.
     * --
     * @param  string   $title The text displayed for input question.
     * @param  function $func  Function to be executed for each input,
     *                         until function is returning null this will run
     *                         in loop.
     * --
     * @return mixed    Value of the function return
     */
    public static function input_multiline($title, $func)
    {
        $result = null;
        $stdin  = '';
        $enter_key = 0;

        do {
            do {
                $stdin_t = self::simple_input($title);
                $stdin .= $stdin_t;
                if ($stdin_t === '') {
                    $enter_key++;
                }
                else {
                    if ($enter_key > 0) {
                        $enter_key--;
                    }
                }
            } while ($enter_key < 1);

            $result = $func($stdin);

        } while($result === null);

        return $result;
    }

    /**
     * Capture the cursos - wait for user's input. You must pass in a function,
     * this will run until function is returning null.
     * The user's input will not be displayed.
     * --
     * @param  string   $title The text displayed for input question.
     * @param  function $func  Function to be executed for each input,
     *                         until function is returning null this will run
     *                         in loop.
     * --
     * @return mixed    Value of the function return
     */
    public static function password($title, $func)
    {
        $result = null;
        `stty -echo`;
        do {
            $stdin = self::simple_input($title);
            $result = $func($stdin);
        } while($result === null);
        `stty echo`;
        return $result;
    }

    /**
     * Ask user a question to while Y/n is the only possible answer.
     * --
     * @param  string  $text
     * @param  boolean $default
     * --
     * @return boolean
     */
    public static function confirm($text, $default = true)
    {
        $question = $text . ' [' . ($default ? 'Y/n' : 'y/N') . '] ';
        return self::input(
            $question,
            function ($input) use ($default) {
                $input = strtolower($input);
                if (empty($input)) {
                    return $default;
                }
                if ($input === 'y') {
                    return true;
                }
                if ($input === 'n') {
                    return false;
                }
            }
        );
    }

    /**
     * Will fill full width of the line with particular character(s).
     * --
     * @param  string $character
     * --
     * @return void
     */
    public static function fill($character)
    {
        $width = (int) exec('tput cols');
        if ($width === 0) { return; }
        $width = floor($width / strlen($character));
        self::plain(str_repeat($character, $width));
    }

    /**
     * Print out the message
     * @param  string  $message
     * @param  boolean $new_line
     */
    public static function warn($message, $new_line=true)
        { return self::out('warn', $message, $new_line); }

    public static function error($message, $new_line=true)
        { return self::out('error', $message, $new_line); }

    public static function plain($message, $new_line=true)
        { return self::out('plain', $message, $new_line); }

    public static function success($message, $new_line=true)
        { return self::out('success', $message, $new_line); }

    /**
     * Create a new line
     * @param  integer $num Number of new lines
     */
    public static function nl($num = 1)
        { fwrite( STDOUT, str_repeat(PHP_EOL, (int) $num) ); }

    /**
     * Create documentation / help for particular command.
     */
    public static function doc($title, $usage, $commands = false)
    {
        self::plain($title);
        self::nl();
        self::plain('  ./dot ' . $usage);
        self::nl();

        if (!is_array($commands)) { return; }

        // Get longest key, to align with it
        $longest = 0;
        foreach ($commands as $key => $command) {
            if (strlen($key) > $longest) {
                $longest = strlen($key);
            }
        }

        // Print all commands
        self::plain('Available options:');
        self::nl();
        foreach ($commands as $key => $command) {
            self::plain('  ' . (!is_integer($key) ? $key : ' ') . '  ', false);
            if (!is_array($command)) {
                $command = [$command];
            }
            foreach ($command as $k => $command_line) {
                $length = $k > 0 ? $longest + 4 : $longest - strlen($key);
                self::plain(str_repeat(" ", $length), false);
                self::plain($command_line);
            }
        }
    }

    /**
     * Will print out the message
     * --
     * @param   string  $type
     *                      plain   -- Regular white message
     *                      error   -- Red message
     *                      warn    -- Yellow message
     *                      success -- Green message
     * @param   string  $message
     * @param   boolean $new_line   Should message be in new line
     */
    public static function out($type, $message, $new_line=true)
    {
        switch (strtolower($type))
        {
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

        if ($new_line) fwrite(STDOUT, PHP_EOL);

        flush();
    }
}
