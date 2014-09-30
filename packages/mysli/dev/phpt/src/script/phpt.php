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
            $param->add('-t/--test', [
                'help'    => 'Run test(s). Package(+:method) to be run '.
                             '(vendor/package or vendor/package:method or '.
                             'vendor/package/class:method)',
                'type'    => 'bool',
                'default' => false
            ]);
            $param->add('-w/--watch', [
                'help'    => 'Watch changes and re-run the test/add command.',
                'type'    => 'bool',
                'default' => false
            ]);
            $param->add('-a/--add', [
                'help'    => 'Scan package/file for methods and create '.
                             'test(s) if needed.',
                'type'    => 'bool',
                'default' => false
            ]);
            $param->add('PACKAGE', [
                'help'     => 'Package name. If not provided, current '.
                              'directory will be used.',
                'required' => false,
                'default'  => null
            ]);

            $param->parse();
            if (!$param->is_valid()) {
                cout::line($param->messages());
            } else {
                self::execute($param->values());
            }
        }
        /**
         * Handle action.
         * @param  array  $args
         * @return null
         */
        private static function execute($args) {
            if (!$args['package']) {
                $package = pkgm::name_from_path(getcwd());
                $pkg = $package;
                $method = null;
            } else {
                $package = $args['package'];
                if (substr($package, 0, 2) === './') {
                    $package = pkgm::name_from_path(getcwd()) .
                               substr($package, 1);
                }
                if (strpos($package, ':')) {
                    list($package, $method) = explode(':', $package, 2);
                }
                if (!isset($method)) {
                    $method = null;
                }
                $pkg = pkgm::name_from_path(fs::pkgpath($package));
            }
            $file = substr($package, strlen($pkg));

            if (!$pkg) {
                cout::warn("Not a valid package: `{$package}`");
                return;
            }

            if ($args['test']) {
                self::run_tests($pkg, $file, $method);
            }
        }

        /**
         * Run test(s) for particular path/file.
         * @param  string $pkg
         * @param  string $file
         * @param  string $method
         * @return null
         */
        private static function run_tests($pkg, $file, $method) {
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
                cout::warn(
                    "No tests found for: `{$pkg}` in `{$spath}:{$method}`");
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
        /**
         * Output diff.
         * @param  array  $diff
         * @return null
         */
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
                cout::info(
                    '~' .
                    str_pad($line+2, 3, '0', STR_PAD_LEFT) . ' ' . $lafter);
            }
        }
    }
}
