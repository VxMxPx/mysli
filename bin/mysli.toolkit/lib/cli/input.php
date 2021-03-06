<?php

namespace mysli\toolkit\cli; class input
{
    const __use = '.cli.output';

    /**
     * Capture the cursor, return user input or function's result.
     * --
     * @param  string   $title The text displayed for input question.
     * @param  callable $call  Execute for each input, until function ($call)
     *                         is returning null.
     * --
     * @return mixed
     */
    static function line($title, $call=null)
    {
        // loop if callback is defined
        if ($call)
        {
            $result = null;
            do
            {
                $stdin  = static::line($title);
                $result = $call($stdin);
            } while($result === null);

            return $result;
        }

        // simple input, no callback
        if (function_exists('readline'))
        {
            $stdin = readline($title);
            readline_add_history($stdin);
        }
        else
        {
            echo $title;
            $stdin = fread(STDIN, 8192);
        }

        return $stdin;
    }

    /**
     * Capture the cursos, wait for user's input.
     * Exit on double enter and function ($call) return null.
     * --
     * @param  string   $title The text displayed for input question.
     * @param  function $call  Execute for each input, until function ($call)
     *                         is returning null.
     * --
     * @return mixed
     */
    static function multiline($title, $call=null)
    {
        $result = null;
        $stdin  = '';
        $enter_key = 0;

        do
        {
            do
            {
                $stdin_t = static::line($title);
                $stdin .= $stdin_t . "\n";

                if ($stdin_t === '')
                {
                    $enter_key++;
                }
                else
                {
                    if ($enter_key > 0)
                    {
                        $enter_key--;
                    }
                }

            } while ($enter_key < 1);

            $result = $call ? $call(trim($stdin)) : null;

        } while($result === null);

        return $result;
    }

    /**
     * Capture the cursor, return user input or function's result.
     * User's input will be hidden.
     * --
     * @param  string   $title The text displayed for input question.
     * @param  function $call  Execute for each input, until function ($call)
     *                         is returning null.
     * --
     * @return mixed
     */
    static function password($title, $call=null)
    {
        $result = null;
        `stty -echo`;
        $result = static::line($title, $call);
        `stty echo`;
        return $result;
    }

    /**
     * Ask user a question to which Y/n is the only possible answer.
     * --
     * @param  string  $title
     * @param  boolean $default
     * --
     * @return boolean
     */
    static function confirm($title, $default=true)
    {
        $question = $title . ' [' . ($default ? 'Y/n' : 'y/N') . '] ';

        return static::line(
            $question,
            function ($input) use ($default)
            {
                $input = strtolower($input);

                if (empty($input))
                {
                    return $default;
                }

                if ($input === 'y')
                {
                    return true;
                }

                if ($input === 'n')
                {
                    return false;
                }
            }
        );
    }

    /**
     * Produce checkbox (allow to select multiple options)
     * --
     * @param  string  $title
     * @param  array   $options
     * @param  array   $checked
     * @param  boolean $compact Display options inline, for example:
     *                          Which tests to create?
     *                          [1] All, 2 Basic, 3 Variation, 4 Error
     *                          Enter one or more number:
     * --
     * @return array
     */
    static function checkbox(
        $title, array $options, array $checked=[], $compact=true)
    {
        $map = array_keys($options);

        foreach ($map as $k => $v)
        {
            $k = $k + 1;
            $question[] = (in_array($v, $checked) ? "[{$k}]" : $k).
                " {$options[$v]}";
        }

        $question = implode(($compact ? ', ' : "\n"), $question);
        $question = "{$title}\n{$question}\nEnter one or more numbers: ";

        return static::line(
            $question,
            function($input) use ($checked, $map)
            {
                if (empty(trim($input)))
                {
                    return $checked;
                }

                $answers = [];

                foreach (explode(' ', $input) as $answer)
                {
                    $answer = (int) trim($answer, ", ")-1;
                    if (!array_key_exists($answer, $map))
                    {
                        output::line("Invalid option: `{$answer}`");
                        return;
                    }
                    else
                    {
                        $answers[] = $map[$answer];
                    }
                }

                return $answers;
            }
        );
    }

    /**
     * Produce radio (buttons) (allow to select only one option)
     * --
     * @param  string  $title
     * @param  array   $options
     * @param  array   $selected
     * @param  boolean $compact Display options inline, for example:
     *                          Which tests to create?
     *                          [1] All, 2 Basic, 3 Variation, 4 Error
     *                          Enter one or more number:
     * --
     * @return mixed
     */
    static function radio(
        $title, array $options, $selected=false, $compact=true)
    {
        $map = array_keys($options);

        foreach ($map as $k => $v)
        {
            $k = $k + 1;
            $question[] = ($v === $selected ? "[{$k}]" : $k)." {$options[$v]}";
        }

        $question = implode(($compact ? ', ' : "\n"), $question);
        $question = "{$title}\n{$question}\nEnter one of the numbers: ";

        return static::line(
            $question,
            function($input) use ($selected, $map)
            {
                if (empty(trim($input)))
                {
                    return $selected;
                }

                $answers = [];

                foreach (explode(' ', $input) as $answer)
                {
                    $answer = (int) trim($answer, ", ")-1;
                    if (!array_key_exists($answer, $map))
                    {
                        output::line("Invalid option: `{$answer}`");
                        return;
                    }
                    else
                    {
                        $answers[] = $map[$answer];
                    }
                }

                return $answers;
            }
        );
    }
}
