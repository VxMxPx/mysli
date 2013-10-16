<?php

namespace Mysli;

class Dot
{

    public function run()
    {
        $this->out_info("Hi there! This is an interactive console for the Mysli CMS.");

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

        $this->out_info('Bye now!');
    }

    /**
     * Print out the message
     * --
     * @param  string  $message
     * @param  boolean $new_line
     */
    private function out_warn($message, $new_line=true)
        { $this->out('warn', $message, $new_line); }

    private function out_error($message, $new_line=true)
        { $this->out('error', $message, $new_line); }

    private function out_info($message, $new_line=true)
        { $this->out('info', $message, $new_line); }

    private function out_success($message, $new_line=true)
        { $this->out('success', $message, $new_line); }

    /**
     * Will print out the message
     * --
     * @param   string  $type
     *                      inf -- Regular white message
     *                      err -- Red message
     *                      war -- Yellow message
     *                      ok  -- Green message
     * @param   string  $message
     * @param   boolean $new_line   Should message be in new line
     */
    private function out($type, $message, $new_line=true)
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

        echo
            (!is_null($color) ? $color : ''),
            $message,
            "\x1b[39;49;00m";

        if ($new_line)
            { echo "\n"; }

        flush();
    }
}