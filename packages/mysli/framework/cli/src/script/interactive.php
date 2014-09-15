<?php

namespace mysli\framework\cli\script {

    __use(__namespace__,
        ['./{output,input}' => 'cout,cin']
    );

    class interactive {
        static function run() {
            cout::info(
                'Hi! This isan interative console for the Mysli Project.');
            cin::line('>> ', function ($stdin) {
                if (in_array(strtolower($stdin), ['exit', 'q'])) {
                    return true;
                }

                if (in_array($stdin, ['help', 'h', '?'])) {
                    self::help();
                    return;
                }

                if (substr($stdin, 0, 1) === '.') {
                    cout::info(eval(substr($stdin, 1)));
                } else {
                    cout::info(eval(
                        'namespace mysli\framework\cli\script\interactive { '.
                        'echo dump_r(' . trim($stdin, ';') . '); }'));
                }
            });
        }
        protected static function help() {
            cout::info('MYSLI INTERACTIVE :: HELP');
            cout::info('Normal PHP commands are accepted, example: `$var = \'value\'`.');
            cout::info('You don\'t need to enter `echo` and you can omit semicolons.');
            cout::info('If you want command not to be echoed automatically, prefix it with dot `.`.');
            cout::info('Enter `q` or `exit` to quit.');
        }
    }
}
