<?php

namespace Mysli\Dot\Script;

class Interactive
{
    public function action_index($args = [])
    {
        \Dot\Util::plain(
            "Hi there! This is an interactive console for the Mysli."
        );

        do {
            if (function_exists('readline')) {
                $stdin = readline('>> ');
                readline_add_history($stdin);
            }
            else {
                echo '>>> ';
                $stdin = fread(STDIN, 8192);
            }
            $stdin = trim($stdin);

            if (in_array(strtolower($stdin), ['exit', 'q'])) {
                break;
            }

            if (in_array($stdin, ['help', 'h', '?'])) {
                $this->help();
                continue;
            }

            if (substr($stdin, 0, 1) === '.') {
                echo "\n" . eval(substr($stdin, 1));
            } else {
                echo "\n" . eval('echo dump_r(' . trim($stdin, ';') . ');');
            }
        } while(true);

        \Dot\Util::plain('Bye now!');
    }

    protected function help()
    {
        \Dot\Util::plain('MYSLI INTERACTIVE :: HELP');
        \Dot\Util::plain('Normal PHP commands are accepted, example: `$var = \'value\'`.');
        \Dot\Util::plain('You don\'t need to enter `echo` and you can omit semicolons.');
        \Dot\Util::plain('If you want command not to be echoed automatically, prefix it with dot `.`.');
        \Dot\Util::plain('Enter `q` or `exit` to quit.');
    }
}
