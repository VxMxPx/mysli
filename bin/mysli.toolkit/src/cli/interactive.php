<?php

namespace mysli\framework\cli\sh;

__use(__namespace__, '
    ./output,input -> cout,cin
');

class interactive
{
    static function __init()
    {
        $namespace = 'namespace mysli\framework\cli\sh\interactive\internal;';
        $multiline = false;
        $buffer = [];

        cout::line('Hi! This isan interative console for the Mysli Project.');
        cin::line('>> ',
            function ($stdin) use ($namespace, &$multiline, &$buffer)
            {
                if ($multiline)
                {
                    cout::line('... ', false);
                }

                if (in_array(strtolower($stdin), ['exit', 'q']))
                {
                    return true;
                }

                if (in_array($stdin, ['help', 'h', '?']))
                {
                    self::help();
                    return;
                }

                if ($stdin === 'START')
                {
                    $multiline = true;
                    $buffer = [$namespace];
                    cout::info('Multiline input set.');
                    cout::line('... ', false);
                    return;
                }

                if ($stdin === 'END')
                {
                    if ($multiline)
                    {
                        $multiline = false;
                        cout::line(eval(implode("\n", $buffer)));
                        return;
                    }
                    else
                    {
                        cout::warn(
                            "Cannot END, you must START multiline input first."
                        );
                        return;
                    }
                }

                if (!$multiline)
                {
                    if (substr($stdin, 0, 1) === '.')
                    {
                        cout::line(eval(substr($stdin, 1)));
                    }
                    else
                    {
                        cout::line(eval(
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

    static function help()
    {
        cout::line('* Mysli CLI Interactive');
        cout::line('    Normal PHP commands are accepted, example: `$var = \'value\'`.');
        cout::line('    You don\'t need to enter `echo` and you can omit semicolons.');
        cout::line('    If you want command not to be echoed automatically, prefix it with dot `.`.');
        cout::line('    Multiline input is allowed, type: `START` to start it, and `END` to execute lines.');
        cout::line('    Enter `q` or `exit` to quit.');
    }
}
