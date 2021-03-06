<?php

namespace mysli\toolkit\root\script; class interactive
{
    const __use = <<<fin
        .cli.{ui, output, input -> cin}
fin;

    /**
     * Run the intective mode.
     */
    static function __run($args)
    {
        $namespace = 'namespace mysli\toolkit\cli\interactive\internal;';
        $multiline = false;
        $buffer = [];

        ui::title('Mysli Interactive Console');

        // See if there's code to be executed...
        $exec = array_search('--exec', $args);

        if (isset($args[$exec+1]))
        {
            $execute = $args[$exec+1];
            ui::line('Execute: '.$execute);
            ui::line(eval(
                $namespace."\n".'echo dump_r(' . trim($execute, ';') . ');'));
        }

        // Now wait for the user input
        cin::line('>> ',
            function ($stdin) use ($namespace, &$multiline, &$buffer)
            {
                if ($multiline)
                    output::line('... ', false);

                if (in_array(strtolower($stdin), ['exit', 'q']))
                    return true;

                if (in_array($stdin, ['help', 'h', '?']))
                {
                    static::help();
                    return;
                }

                if ($stdin === 'START')
                {
                    $multiline = true;
                    $buffer = [$namespace];
                    ui::info('Multiline input set.');
                    output::line('... ', false);
                    return;
                }

                if ($stdin === 'END')
                {
                    if ($multiline)
                    {
                        $multiline = false;
                        ui::line(eval(implode("\n", $buffer)));
                        return;
                    }
                    else
                    {
                        ui::warning(
                            'WARNING',
                            "Cannot END, you must START multiline input first.");
                        return;
                    }
                }

                if (!$multiline)
                {
                    if (substr($stdin, 0, 1) === '.')
                    {
                        ui::line(eval(substr($stdin, 1)));
                    }
                    else
                    {
                        ui::line(eval(
                            $namespace."\n".
                            'echo dump_r(' . trim($stdin, ';') . ');'));
                    }
                }
                else
                {
                    $buffer[] = $stdin;
                }
            }
        );
    }

    /**
     * Display help message.
     */
    private static function help()
    {
        ui::title('Mysli CLI Interactive');
        ui::line(<<<HELP
Normal PHP commands are accepted, example: `\$var = 'value'`.
You don't need to enter `echo` and you can omit semicolons.
If you want command not to be echoed automatically, prefix it with dot `.`.
Multiline input is allowed, type: `START` to start it, and `END` to execute lines.
Enter `q` or `exit` to quit.

Use --exec CODE to execute code on start.
HELP
        );
    }
}
