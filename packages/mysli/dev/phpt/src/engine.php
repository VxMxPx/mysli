<?php

/**
 *  This class is partly based on (/inspied by) run-tests.php by The PHP Group.
 *  -------------------------------------
 *  Copyright (c) 1997-2010 The PHP Group
 *  This function is subject to version 3.01 of the PHP license,
 *  that is bundled with this package in the file LICENSE, and is
 *  available through the world-wide-web at the following url:
 *  http://www.php.net/license/3_01.txt
 *  If you did not receive a copy of the PHP license and are unable to
 *  obtain it through the world-wide-web, please send a note to
 *  license@php.net so we can mail you a copy immediately.
 */
namespace mysli\dev\phpt {
    class engine {
        /**
         * Execute PHP command
         * @param  string $command
         * @param  mixed  $env
         * @param  string $cwd
         * @param  string $stdin
         * @return string
         */
        static function run($command, $env, $cwd, $stdin=null) {

            $data = '';
            $bin_env = [];

            foreach((array) $env as $key => $value) {
                $bin_env[$key] = $value;
            }

            $proc = proc_open($command, [
                    0 => array('pipe', 'r'),
                    1 => array('pipe', 'w'),
                    2 => array('pipe', 'w')
                ], $pipes, $cwd, $bin_env,
                array('suppress_errors' => true, 'binary_pipes' => true));

            if (!$proc) {
                return false;
            }

            if (!is_null($stdin)) {
                fwrite($pipes[0], $stdin);
            }
            fclose($pipes[0]);
            unset($pipes[0]);

            $timeout = 60;

            while (true) {
                // hide errors from interrupted syscalls
                $r = $pipes;
                $w = null;
                $e = null;

                $n = @stream_select($r, $w, $e, $timeout);

                if ($n === false) {
                    break;
                } elseif ($n === 0) {
                    // timed out
                    $data .= "\nERROR: process timed out!\n";
                    proc_terminate($proc, 9);
                    return $data;
                } elseif ($n > 0) {
                    $line = fread($pipes[1], 8192);
                    if (strlen($line) == 0) {
                        /* EOF */
                        break;
                    }
                    $data .= $line;
                }
            }

            $stat = proc_get_status($proc);

            if ($stat['signaled']) {
                $data .= "\nTermsig=" . $stat['stopsig'];
            }

            $code = proc_close($proc);
            return $data;
        }
    }
}
