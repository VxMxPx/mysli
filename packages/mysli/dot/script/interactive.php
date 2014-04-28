<?php

namespace Mysli\Dot\Script;

class Interactive
{
    protected $pkgm;

    public function __construct($pkgm)
    {
        $this->pkgm = $pkgm;
    }

    public function action_index()
    {
        $pkgm = $this->pkgm;

        \Cli\Util::plain(
            "Hi there! This is an interactive console for the Mysli Project."
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

        \Cli\Util::plain('Bye now!');
    }

    protected function help()
    {
        \Cli\Util::plain('MYSLI INTERACTIVE :: HELP');
        \Cli\Util::plain('Normal PHP commands are accepted, example: `$var = \'value\'`.');
        \Cli\Util::plain('You don\'t need to enter `echo` and you can omit semicolons.');
        \Cli\Util::plain('If you want command not to be echoed automatically, prefix it with dot `.`.');
        \Cli\Util::plain('You can use package manager to get packages: `$pkgm->factory`...');
        \Cli\Util::plain('Enter `q` or `exit` to quit.');
    }
}
