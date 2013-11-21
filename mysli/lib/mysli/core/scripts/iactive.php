<?php

namespace Mysli\Core\Script;

class Iactive
{
    public function action_index($args = [])
    {
        \DotUtil::plain("Hi there! This is an interactive console for the Mysli CMS.");

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

            echo "\n" . eval('echo dump_r(' . $stdin . ');');
        } while(true);

        \DotUtil::plain('Bye now!');
    }
}
