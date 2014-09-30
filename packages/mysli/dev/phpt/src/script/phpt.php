<?php

namespace mysli\dev\phpt\script {

    __use(__namespace__,
        './{collection,generator}',
        ['mysli/framework' => [
                'fs/{fs,file,dir}',
                'pkgm',
                'cli/{param,output,input}' => 'param,cout,cin'
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

            if ($args['watch']) {
                self::watch($pkg, $file, $method, $args['add'], $args['test']);
                return;
            }
            if ($args['add']) {
                self::add_test($pkg, $file, true);
            }
            if ($args['test']) {
                self::run_test($pkg, $file, $method);
            }
        }

        /**
         * Watch files for changes and re-run/re-add tests when changes occurs.
         * @param  string  $pkg
         * @param  string  $file
         * @param  string  $method
         * @param  boolean $do_add
         * @param  boolean $do_test
         * @param  integer $sleep
         */
        private static function watch($pkg, $file, $method,
                                      $do_add, $do_test, $sleep=2) {
            // Add files path
            $sfp = [fs::pkgpath(fs::ds("{$pkg}/src")),
                    ($file
                        ? "/".preg_quote(trim($file,'/\\'))."\\.php/"
                        : '/.*?\\.php/')];
            // Test files path
            $tfp = [fs::pkgpath(fs::ds($pkg, $file)),
                    ($method
                        ? "/".preg_quote(trim($method,'/\\'))."_[a-z]+\\.phpt/"
                        : '/.*?\\.phpt/')];

            $diff          = false;
            $last_src_hash = null;
            $last_tst_hash = null;

            while (true) {
                if ($do_add) {
                    $src_files = file::find($sfp[0], $sfp[1]);
                    $src_files_hash = file::signature($src_files);
                    $src_hash  = md5(implode('', $src_files_hash));
                    if ($src_hash !== $last_src_hash) {
                        foreach ($src_files_hash as $id => $sig) {
                            $src_file = substr(
                                $src_files[$id], strlen($sfp[0])+1, -4);
                            self::add_test($pkg, $file);
                        }
                        $diff = true;
                        $last_src_hash = $src_hash;
                    }
                }
                if ($do_test) {
                    if (!$diff || !$last_tst_hash) {
                        $tst_files = file::find($tfp[0], $tfp[1]);
                        $tst_files_hash = file::signature($tst_files);
                        $tst_hash = md5(implode('', $tst_files_hash));
                        if ($tst_hash !== $last_tst_hash) {
                            $last_tst_hash = $tst_hash;
                            self::run_test($pkg, $file, $method);
                        }
                    } else {
                        self::run_test($pkg, $file, $method);
                    }
                }
                $diff = false;
                sleep($sleep);
            }
        }
        /**
         * Add test(s) for particular file/path.
         * @param string  $pkg
         * @param string  $file
         * @param boolean $ask
         */
        private static function add_test($pkg, $file, $ask=true) {
            if ($file === '/' || !$file) {
                $root = fs::pkgpath($pkg,'/src');
                foreach (file::find($root, '/\\.php$/', true) as $file) {
                    $file = substr($file, strlen($root), -4);
                    if ($file === 'setup') {
                        continue;
                    }
                    self::add_test($pkg, $file, $ask);
                }
                return;
            }
            $spath = fs::ds($pkg, 'tests', $file);
            $path = fs::pkgpath($pkg, 'tests', $file);
            $abs_file = fs::pkgpath($pkg, 'src', $file.'.php');
            if (!file_exists($abs_file)) {
                cout::warn("File not found: `{$abs_file}`");
                return;
            }

            // Get methods
            try {
                $tests = generator::get_methods($abs_file);
            } catch (\Exception $e) {
                cout::warn("[Failed] " . $e->getMessage());
                return;
            }

            if (!isset($tests['methods']) || empty($tests['methods'])) {
                cout::info("No methods found: `{$abs_file}`");
                return;
            }

            // Get existing files
            if (!dir::exists($path)) {
                cout::warn("Directory not found: `{$spath}`");
                $create_dir = $ask
                    ? cin::confirm("Create it now?")
                    : true;
                if (!$create_dir) {
                    if (cin::confirm("Ignore it in future?")) {
                        dir::create($path);
                        file::write(fs::ds($path, 'ignore'), '');
                    } else {
                        cout::info('Terminated');
                    }
                    return;
                } else {
                    if (!dir::create($path)) {
                        cout::error("Failed to create: `{$spath}`");
                        return;
                    }
                }
            }

            if (file::exists(fs::ds($path, 'ignore'))) {
                return;
            }

            $files = file::find($path, '/\\.(phpt|ignore|delete)$/', true);

            // Delete
            $do_delete = []; // list of files for which deletion was confirmed
            $found     = [];
            foreach ($files as $tf) {
                $ts     = str_replace('\\', '/', $tf);
                $ext    = file::extension($tf);
                $method = substr($tf, 0, -(strlen($ext)+1));
                $method = substr($method, strrpos($method, '/')+1);
                $method_type = substr($method, strrpos($method, '_')+1);
                $method = substr($method, 0, -(strlen($method_type)+1));
                $prefix = substr($tf,
                            strrpos($tf, 'tests/')+6,
                            -(strlen("{$method}_{$method_type}.{$ext}")+1));
                if ($ext === 'ignore' || $ext === 'delete') {
                    cout::info("Method: `{$method}` is set to: `{$ext}`");
                    $found[] = $method;
                    continue;
                }
                if (!isset($tests['methods'][$method])) {
                    cout::info("Method `{$method}` doesn't exists anymore.");
                    $set_deleted = ($ask || in_array($method, $do_delete))
                        ? cin::confirm("Set tests to delete?")
                        : true;
                    if ($set_deleted) {
                        $do_delete[] = $method;
                        if (!file::rename($tf,
                                          "{$method}_{$method_type}.delete")) {
                            cout::warn("Couldn't rename: `{$method}_".
                                       "{$method_type}.{$ext}` to `{$method}_".
                                       "{$method_type}.delete`");
                        }
                    }
                } else {
                    $found[] = $method;
                }
            }

            // Create
            if (!empty($tests)) {
                foreach ($tests['methods'] as $method => $opt) {
                    if ($opt['visibility'] !== 'public') {
                        continue;
                    }
                    if (in_array($method, $found)) {
                        continue;
                    }
                    cout::info("Test for `{$method}` doesn't exists.");
                    if (isset($opt['description']))
                        $o = ['description' => $opt['description']];
                    $o['file'] = "<?php\n".
                                 "use {$tests['namespace']}\\".
                                 "{$tests['class']};\n?>";
                    $o['skipif'] = '<?php die("Write test..."); ?>';
                    $t = generator::make($o);
                    $tc = $ask
                        ? cin::checkbox(
                            'Which tests should be added?',
                            ['All', 'Basic', 'Error', 'Variation', 'None', 'Ignore'],
                            [0], true)
                        : [0];
                    if (!in_array(4, $tc) &&
                        (in_array(0, $tc) || in_array(1, $tc)))
                        file::write(
                            fs::ds($path, $method.'_basic.phpt'), $t);
                    if (!in_array(4, $tc) &&
                        (in_array(0, $tc) || in_array(2, $tc)))
                        file::write(
                            fs::ds($path, $method.'_error.phpt'), $t);
                    if (!in_array(4, $tc) &&
                        (in_array(0, $tc) || in_array(3, $tc)))
                        file::write(
                            fs::ds($path, $method.'_variation.phpt'), $t);
                    if (in_array(4, $tc))
                        cout::info("No tests will be created.");
                    if (in_array(5, $tc)) {
                        file::write(
                            fs::ds($path, $method.'_all.ignore'),
                            "Auto Generated on: " . time());
                    }
                }
            }
        }
        /**
         * Run test(s) for particular path/file.
         * @param  string $pkg
         * @param  string $file
         * @param  string $method
         */
        private static function run_test($pkg, $file, $method) {
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
