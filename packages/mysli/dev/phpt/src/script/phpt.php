<?php

namespace mysli\dev\phpt\script {

    __use(__namespace__,
        './collection',
        ['mysli/framework' => [
                'fs/{fs,file,dir}',
                'pkgm',
                'cli/{param,output}' => 'param,cout'
            ]
        ]
    );

    class phpt {
        /**
         * Execute script.
         * @param  array $args
         * @return null
         */
        static function run($args) {
            $param = new param('Mysli PHPT Wrapper', $args);
            $param->command = 'phpt';
            $param->add('-p/--package', [
                'help'   => 'Package(+:method) to be observed (vendor/package '.
                            'or vendor/package:method or '.
                            'vendor/package/class:method)',
                'exclude'=> 'current',
                'invoke' => __namespace__.'\\phpt::package'
            ]);
            $param->add('-m/--method', [
                'help'    => 'Method to be observed',
                'default' => null
            ]);
            $param->add('-w/--watch', [
                'help'    => 'Watch package for changes',
                'type'    => 'bool',
                'default' => false
            ]);
            $param->add('-a/--add', [
                'help'    => 'Scan package for methods and create '.
                             'test(s) if needed',
                'type'    => 'bool',
                'default' => false
            ]);
            $param->add('-c/--current', [
                'help'    => 'Use current directory as a package (replace -p)',
                'allow_empty' => true,
                'invoke'  => __namespace__.'\\phpt::package',
                'action'  => function (&$value, &$is_valid, &$messages) {
                    $package = pkgm::name_from_path(getcwd());
                    if (!$package) {
                        $messages[] = "Not in a valid package directory!";
                        $is_valid = false;
                    }
                    if (substr($value, 0, 4) === 'src/') {
                        $value = substr($value, 4);
                    }
                    if (substr($value, -4) === '.php') {
                        $value = substr($value, 0, -4);
                    }
                    $value = fs::ds($package, $value);
                }
            ]);

            $param->parse();
            if (!$param->is_valid()) { cout::line($param->messages()); }
        }
        /**
         * Run test(s) for particular package.
         * @param  string $package
         * @param  array  $args
         * @return null
         */
        static function package($package, $args) {
            if ($args['add']) {
                // -------------------------------------------------------------
                // /home/m/www/packages/mysli/dev/phpt/src/file.php
                // /home/m/www/packages/mysli/dev/phpt/tests/file/
                //      method_basic.ignore
                //      method_error.ignore
                // -------------------------------------------------------------
                // $creator = creator::produce($path);
                // $creator->scan();
                // -------------------------------------------------------------
                cout::yellow('ADD was called!');
                return;
            }
            if (strpos($package, ':')) {
                list($package, $method) = explode(':', $package, 2);
            }
            if ($args['method']) {
                $method = $args['method'];
            }
            if (!isset($method)) {
                $method = null;
            }
            $pkg = pkgm::name_from_path(fs::pkgpath($package));
            $file = substr($package, strlen($pkg));
            // if (!$file) {
            //     $file = rtrim( substr($pkg, strrpos($pkg, '/')), '/') . '.php';
            // }

            if (!$pkg) {
                cout::warn("Not a valid package: `{$package}`");
                return;
            }

            $spath = fs::ds($pkg, 'tests', $file);
            $path = fs::pkgpath($pkg, 'tests', $file);

            if (!dir::exists($path)) {
                cout::warn("No tests available for: `{$pkg}` in `{$spath}`");
                return;
            }
            if ($method) {
                $method = "{$method}*phpt";
            } else {
                $method = '*.phpt';
            }

            $tests = new collection(fs::ds($path, $method));

            if (!count($tests)) {
                cout::warn("No tests found for: `{$pkg}` in `{$spath}:{$method}`");
                return;
            }

            foreach ($tests as $test) {
                $test->execute();
                $test->cleanup();
                cout::line("TEST [{$test->filename()}]", false);
                if ($test->succeed()) {
                    cout::format('+right+green%s', ['OK']);
                } elseif ($test->skipped()) {
                    cout::format('+right+yellow%s', ['SKIPPED']);
                    cout::info($test->skipped_message());
                } else {
                    cout::format('+right+red%s', ['FAILED']);
                    self::diff_out($test->diff());
                }
            }

            cout::fill('-');
            $total    = count($tests->executed());
            $failed   = count($tests->failed());
            $success  = count($tests->success());
            $skipped  = count($tests->skipped());
            $run_time =round($tests->run_time(), 4);

            cout::format(
                'RUN: %s | '.
                ($failed  ? '+red FAILED: %s-red  | '       : 'FAILED: %s | ').
                (!$failed ? '+green SUCCEED: %s-green  | '  : 'SUCCEED: %s | ').
                ($skipped ? '+yellow SKIPPED: %s-yellow  | ': 'SKIPPED: %s | ').
                "TOTAL TIME: %s",
                [$total, $failed, $success, $skipped, $run_time]);
        }

        private static function diff_out(array $diff) {
            $last = -1;
            foreach ($diff as $k => $diff_line) {
                list($line, $symbol, $lbefore, $value, $lafter) = $diff_line;
                if ($last !== $line) {
                    cout::info(
                        '~' . str_pad($line, 3, '0', STR_PAD_LEFT) .
                        ' ' . $lbefore);
                }
                $last = $line+1;
                cout::format('+'.($symbol==='+'?'green':'red').'%s%s %s', [
                             $symbol,
                             str_pad($line+1, 3, '0', STR_PAD_LEFT),
                             $value]);
                if (isset($diff[$k+1])) {
                    if (($diff[$k+1][0]+1) === ($line+2)) {
                        continue;
                    }
                }
                cout::info('~' . str_pad($line+2, 3, '0', STR_PAD_LEFT) . ' ' . $lafter);
            }
        }
    }
}
