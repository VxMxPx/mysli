<?php

namespace mysli\framework\cli\sh;

__use(__namespace__, '
    ./output,input -> cout,cin
');

class interactive
{
    static function __init()
    {
        cout::line('Hi! This isan interative console for the Mysli Project.');
        cin::line('>> ', function ($stdin)
        {
            if (in_array(strtolower($stdin), ['exit', 'q']))
            {
                return true;
            }

            if (in_array($stdin, ['help', 'h', '?']))
            {
                self::help();
                return;
            }

            if (substr($stdin, 0, 1) === '.')
            {
                cout::line(eval(substr($stdin, 1)));
            }
            else
            {
                cout::line(eval(
                    'namespace mysli\framework\cli\sh\interactive\internal { '.
                    'echo dump_r(' . trim($stdin, ';') . '); }'));
            }
        });
    }

    static function help()
    {
        cout::line('* Mysli CLI Interactive');
        cout::line('    Normal PHP commands are accepted, example: `$var = \'value\'`.');
        cout::line('    You don\'t need to enter `echo` and you can omit semicolons.');
        cout::line('    If you want command not to be echoed automatically, prefix it with dot `.`.');
        cout::line('    Enter `q` or `exit` to quit.');
    }
}
