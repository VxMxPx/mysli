<?php

namespace mysli\dev\phpt\sh;

__use(__namespace__, '
    ./collection,generator
    mysli.framework.fs/fs,file,dir
    mysli.framework.cli/param,output,input,util -> param,cout,cin,cutil
');

class phpt
{
    /**
    * Execute script.
    * @param  array $args
    * @return boolean
    */
    static function __init(array $args)
    {
        $param = new param('Mysli PHPT Wrapper', $args);
        $param->command = 'phpt';
        $param->add('-t/--test', [
            'help'    => 'Run test(s). Package(+:method) to be run '.
                         '(vendor.package or vendor.package:method or '.
                         'vendor.package/class:method)',
            'type'    => 'bool',
            'default' => false
        ]);
        $param->add('-w/--watch', [
            'help'    => 'Watch changes and re-run the test/add command.',
            'type'    => 'bool',
            'default' => false
        ]);
        $param->add('-a/--add', [
            'help'    => 'Scan package/file for methods and create test(s) if needed.',
            'type'    => 'bool',
            'default' => false
        ]);
        $param->add('--std-diff', [
            'help'    => 'Print standard diff, rather than side-by-side version.',
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

        if (!$param->is_valid())
        {
            cout::line($param->messages());
            return true;
        }

        return self::execute($param->values());
    }
    /**
    * Handle action.
    * @param  array $args
    * @return boolean
    */
    static function execute($args)
    {
        $method = null;
        $file = null;

        if (!$args['package'])
        {
            // Package not provided, should be acquired from current directory
            $package = \core\pkg::by_path(getcwd());
        }
        else
        {
            $package = $args['package'];

            if (substr($package, 0, 2) === './')
            {
                $package = \core\pkg::by_path(getcwd()).substr($package, 1);
            }

            if (strpos($package, ':'))
            {
                list($package, $method) = explode(':', $package, 2);
            }

            if (strpos($package, '/'))
            {
                list($package, $file) = explode('/', $package, 2);
            }
        }

        if (!$package)
        {
            cout::warn("[!] Not a valid package provided.");
            return false;
        }

        if ($args['watch'])
        {
            return self::watch(
                $package,
                $file,
                $method,
                $args['add'],
                $args['test'],
                !!$args['std-diff']
            );
        }

        if ($args['add'])
        {
            return self::add_test($package, $file, true);
        }

        if ($args['test'])
        {
            return self::run_test($package, $file, $method, !!$args['std-diff']);
        }
    }
    /**
    * Watch files for changes and re-run/re-add tests when changes occurs.
    * @param  string  $pkg
    * @param  string  $file
    * @param  string  $method
    * @param  boolean $do_add
    * @param  boolean $do_test
    * @param  boolean $std_diff
    * @param  integer $sleep
    */
    static function watch($pkg, $file, $method, $do_add, $do_test, $std_diff, $sleep=2)
    {
        // Add files path
        // $sfp = [
        //     fs::pkgreal($pkg, 'src/php'),
        //     ($file
        //         ? "/".preg_quote(trim($file,'/\\'), '/')."\\.php/"
        //         : '/.*?\\.php/'
        //     )
        // ];
        $sfp = [
            fs::pkgreal($pkg, 'src/php'),
            '/.*?\\.php/'
        ];

        // Test files path
        $tfp = [
            fs::pkgreal($pkg, 'tests', $file),
            ($method
                ? "/".preg_quote(trim($method,'/\\'), '/')."[a-z0-9_]*?\\.[a-z]+/"
                : '/.*?\\.[a-z]+/'
            )
        ];

        $diff          = false;
        $last_src_hash = null;
        $last_tst_hash = null;

        while (true)
        {
            if (!dir::exists($sfp[0]))
            {
                cout::warn("Not a valid directory: `{$sfp[0]}`");
            }
            else
            {
                $src_files      = file::find($sfp[0], $sfp[1]);
                $src_files_hash = file::signature($src_files);
                $src_hash       = md5(implode('', $src_files_hash));

                if ($src_hash !== $last_src_hash)
                {
                    if ($do_add)
                    {
                        foreach ($src_files_hash as $id => $sig)
                        {
                            $src_file = substr($id, strlen($sfp[0])+1, -4);
                            self::add_test($pkg, $src_file);
                        }
                    }

                    $diff = true;
                    $last_src_hash = $src_hash;
                }
            }

            if ($do_test)
            {
                if (!$diff || !$last_tst_hash)
                {
                    if (!dir::exists($tfp[0]))
                    {
                        cout::warn("Not a valid directory: `{$tfp[0]}`");
                    }
                    else
                    {
                        $tst_files      = file::find($tfp[0], $tfp[1]);
                        $tst_files_hash = file::signature($tst_files);
                        $tst_hash       = md5(implode('', $tst_files_hash));

                        if ($tst_hash !== $last_tst_hash)
                        {
                            $last_tst_hash = $tst_hash;
                            self::run_test($pkg, $file, $method, $std_diff);
                        }
                    }
                }
                else
                {
                    self::run_test($pkg, $file, $method, $std_diff);
                }
            }

            $diff = false;
            sleep($sleep);
        }
    }
    /**
    * Add test(s) for particular file/path.
    * @param  string  $pkg
    * @param  string  $file
    * @param  boolean $ask
    * @return boolean
    */
    static function add_test($pkg, $file, $ask=true)
    {
        if ($file === '/' || !$file)
        {
            $root = fs::pkgreal($pkg, 'src/php');

            foreach (file::find($root, '/\\.php$/', true) as $file)
            {
                $file = substr($file, strlen($root), -4);

                if ($file === '__init')
                {
                    continue;
                }

                self::add_test($pkg, $file, $ask);
            }

            return true;
        }

        $spath = fs::ds($pkg, 'tests', $file);
        $path = fs::pkgreal($pkg, 'tests', $file);
        $abs_file = fs::pkgreal($pkg, 'src/php', $file.'.php');

        if (!file_exists($abs_file))
        {
            cout::warn("File not found: `{$abs_file}`");
            return false;
        }

        // Get methods
        try
        {
            $tests = generator::get_methods($abs_file);
        }
        catch (\Exception $e)
        {
            cout::warn("[Failed] ".$e->getMessage());
            return false;
        }

        if (!isset($tests['methods']) || empty($tests['methods']))
        {
            cout::info("No methods found: `{$abs_file}`");
            return false;
        }

        // Get existing files
        if (!dir::exists($path))
        {
            cout::warn("Directory not found: `{$spath}`");

            $create_dir = $ask
                ? cin::confirm("Create it now?")
                : true;

            if (!$create_dir)
            {
                if (cin::confirm("Ignore it in future?"))
                {
                    dir::create($path);
                    file::write(fs::ds($path, 'ignore'), '');
                }
                else
                {
                    cout::info('Terminated');
                }

                return true;
            }
            else
            {
                if (!dir::create($path))
                {
                    cout::error("Failed to create: `{$spath}`");
                    return false;
                }
            }
        }

        if (file::exists(fs::ds($path, 'ignore')))
        {
            return true;
        }

        $files = file::find($path, '/\\.(phpt|ignore|delete)$/', true);

        // Delete
        $do_delete = []; // list of files for which deletion was confirmed
        $found     = [];

        foreach ($files as $handle => $tf)
        {
            $ts     = str_replace('\\', '/', $tf);
            $ext    = file::extension($tf);
            $method = substr($tf, 0, -(strlen($ext)+1));
            $method = substr($method, strrpos($method, '/')+1);

            // no strict checking, because 0, e.g.: __construct doesn't count!
            if (!strrpos($method, '__'))
            {
                cout::warn(
                    "Warning: `{$method}`; filename format is invalid. ".
                    "Required format: `method__description.phpt`."
                );
                continue;
            }

            $method_type = substr($method, strrpos($method, '__')+1);
            $method = substr($method, 0, -(strlen($method_type)+1));
            $prefix = substr(
                $tf,
                strrpos($tf, 'tests/')+6,
                -(strlen("{$method}__{$method_type}.{$ext}")+1)
            );

            if ($ext === 'ignore' || $ext === 'delete')
            {
                cout::info("Method: `{$method}` is set to: `{$ext}`");
                $found[] = $method;
                continue;
            }

            if (!isset($tests['methods'][$method]))
            {
                cout::info("Method `{$method}` doesn't exists!");

                $set_deleted = ($ask || in_array($method, $do_delete))
                    ? cin::confirm("Set tests to delete?")
                    : true;

                if ($set_deleted)
                {
                    $do_delete[] = $method;

                    if (!file::rename($tf, "{$method}__{$method_type}.delete"))
                    {
                        cout::warn(
                            "Couldn't rename: `{$method}__{$method_type}.{$ext}` ".
                            "to `{$method}__{$method_type}.delete`"
                        );
                    }
                }
            }
            else
            {
                $found[] = $method;
            }
        }

        // Create
        if (!empty($tests))
        {
            foreach ($tests['methods'] as $method => $opt)
            {
                if ($opt['visibility'] !== 'public')
                {
                    continue;
                }

                if (in_array($method, $found))
                {
                    continue;
                }

                cout::info("Test for `{$method}` doesn't exists.");

                if (isset($opt['description']))
                {
                    $o = ['description' => $opt['description']];
                }

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

                if (!in_array(4, $tc) && (in_array(0, $tc) || in_array(1, $tc)))
                {
                    file::write(fs::ds($path, $method.'__basic.phpt'), $t);
                }

                if (!in_array(4, $tc) && (in_array(0, $tc) || in_array(2, $tc)))
                {
                    file::write(fs::ds($path, $method.'__error.phpt'), $t);
                }

                if (!in_array(4, $tc) && (in_array(0, $tc) || in_array(3, $tc)))
                {
                    file::write(fs::ds($path, $method.'__variation.phpt'), $t);
                }

                if (in_array(4, $tc))
                {
                    cout::info("No tests will be created.");
                }

                if (in_array(5, $tc))
                {
                    file::write(
                        fs::ds($path, $method.'__all.ignore'),
                        "Auto Generated on: " . time()
                    );
                }
            }
        }
    }
    /**
    * Run test(s) for particular path/file.
    * @param  string  $pkg
    * @param  string  $file
    * @param  string  $method
    * @param  boolean $std_diff
    * @return boolean
    */
    static function run_test($pkg, $file, $method, $std_diff)
    {
        $spath = fs::ds(str_replace('.', '/', $pkg), 'tests', $file);
        $path  = fs::pkgreal($pkg, 'tests', $file);

        cout::nl();
        cout::fill('=');

        if (!dir::exists($path))
        {
            cout::warn("No tests available for: `{$pkg}` in `{$spath}`");
            return false;
        }

        if ($method)
        {
            $method = "{$method}*phpt";
        }
        else
        {
            $method = '*.phpt';
        }

        $tests = new collection(fs::ds($path, $method));

        if (!count($tests))
        {
            cout::warn("No tests found for: `{$pkg}` in `{$spath}{$method}`");
            return false;
        }

        foreach ($tests as $test)
        {
            try
            {
                $test->execute();
                $test->cleanup();
            }
            catch (\Exception $e)
            {
                cout::warn('Failed with message: ' . $e->getMessage());
                cout::warn('Will skip: `' . $test->filename() . '`');
                continue;
            }


            if ($test->succeed())
            {
                cout::format("<green>SUCCEED:</green> {$test->filename()}\n");
            }
            elseif ($test->skipped())
            {
                cout::format("<yellow>SKIPPED:</yellow> {$test->filename()}\n");
                cout::info($test->skipped_message());
            }
            else
            {
                cout::format("<red>FAILED:</red> {$test->filename()}\n");
                self::diff_out($test->diff(), $std_diff);
            }
        }

        cout::fill('-');
        $total    = count($tests->executed());
        $failed   = count($tests->failed());
        $success  = count($tests->success());
        $skipped  = count($tests->skipped());
        $run_time = round($tests->run_time(), 4);

        cout::format(
            'RUN: %s | '.
            ($failed  ? '<red>FAILED: %s</red> | '        : 'FAILED: %s | ').
            (!$failed ? '<green>SUCCEED: %s</green> | '   : 'SUCCEED: %s | ').
            ($skipped ? '<yellow>SKIPPED: %s</yellow> | ' : 'SKIPPED: %s | ').
            "TOTAL TIME: %s\n",
            [$total, $failed, $success, $skipped, $run_time]
        );

        if ($failed > 0)
        {
            return false;
        }
        else
        {
            return true;
        }
    }
    /**
    * Output diff.
    * @param  array   $diff
    * @param  boolean $std display standard diff
    * @return null
    */
    static function diff_out(array $diff, $std)
    {
        if ($std)
        {
            self::std_diff($diff);
        }
        else
        {
            self::side_by_side_diff($diff);
        }
    }

    /**
     * Standard diff.
     * @param  array $diff
     */
    static function std_diff(array $diff)
    {
        $last = -1;

        foreach ($diff as $k => $diff_line)
        {
            list($line, $symbol, $lbefore, $value, $lafter) = $diff_line;

            if ($last !== $line)
            {
                cout::info(
                    '~' . str_pad($line, 3, '0', STR_PAD_LEFT) .
                    ' ' . $lbefore
                );
            }

            $last = $line+1;

            cout::format(
                '<'.($symbol==='+'?'green':'red').">%s%s %s\n",
                [
                    $symbol,
                    str_pad($line+1, 3, '0', STR_PAD_LEFT),
                    $value
                ]
            );

            if (isset($diff[$k+1]))
            {
                if (($diff[$k+1][0]+1) === ($line+2))
                {
                    continue;
                }
            }

            cout::info('~'.str_pad($line+2, 3, '0', STR_PAD_LEFT).' '.$lafter);
        }
    }

    /**
     * Display side | by | side diff.
     * @param array  $diff
     */
    static function side_by_side_diff(array $diff)
    {
        $term_width   = cutil::terminal_width();
        $term_half    = floor($term_width / 2);
        $longest_num  = 0;
        $longest_line = 15;
        $diff_side    = [];

        foreach ($diff as $diff_line)
        {
            list($line_num, $symbol, $_, $lexpect, $_) = $diff_line;

            if (!isset($diff_side[$line_num]))
            {
                $diff_side[$line_num] = ['-' => null, '+' => null];
            }

            if (strlen($line_num) > $longest_num)
            {
                $longest_num = strlen($line_num);
            }

            $diff_side[$line_num][$symbol] = $lexpect;

            if (strlen($lexpect) > $longest_line)
            {
                $longest_line = strlen($lexpect);
            }
        }

        // Adjust term half...
        $term_half = $term_half - $longest_num - 5;

        if ($longest_line > $term_half)
        {
            $longest_line = $term_half;
        }

        // Header
        cout::line('L'.str_repeat(' ', $longest_num-1).'| ', false);
        cout::green(str_pad(substr('Expected', 0, $longest_line), $longest_line, ' '), false);
        cout::line(' | ', false);
        cout::red(str_pad(substr('Actual', 0, $longest_line), $longest_line, ' '), true);

        // Divider
        $divider = str_repeat('-', $longest_num).'+'.
            str_repeat('-', $longest_line+2).'+'.
            str_repeat('-', $longest_line);

        cout::line($divider);

        foreach ($diff_side as $line_num => $diff_line)
        {
            $line  = str_pad($line_num, $longest_num, '0', STR_PAD_LEFT);
            $line .= '| ';

            if ($diff_line['-'] === null && empty($diff_line['+']))
            {
                $diff_line['+'] = '<-NL';
            }

            // Adjust both strings lengths
            if (strlen($diff_line['+']) > strlen($diff_line['-']))
            {
                $diff_line['-'] = str_pad(
                    $diff_line['-'],
                    strlen($diff_line['+']),
                    ' ',
                    STR_PAD_RIGHT
                );
            }
            else
            {
                $diff_line['+'] = str_pad(
                    $diff_line['+'],
                    strlen($diff_line['-']),
                    ' ',
                    STR_PAD_RIGHT
                );
            }

            // Break both strings to chunkcs
            $diff_line['+'] = str_split($diff_line['+'], $longest_line);
            $diff_line['-'] = str_split($diff_line['-'], $longest_line);

            cout::line($line, false);
            $ci = 0;

            foreach ($diff_line["+"] as $ci => $diff_c1)
            {
                $diff_c2 = $diff_line['-'][$ci];

                if (strlen($diff_c1) < $longest_line)
                {
                    $diff_c1 = str_pad($diff_c1, $longest_line, ' ', STR_PAD_RIGHT);
                    $diff_c2 = str_pad($diff_c2, $longest_line, ' ', STR_PAD_RIGHT);
                }

                if ($ci > 0)
                {
                    $diff_c2 = str_repeat(' ', $longest_num).'| '.$diff_c2;
                }

                cout::green($diff_c2, false);
                cout::line(' | ', false);
                cout::red($diff_c1, true);
            }

            // We had multiple lines...
            if ($ci > 0)
            {
                cout::line(
                    str_repeat(' ', $longest_num).'| '.
                    str_repeat(' ', $longest_line).' |'
                );
            }
        }

        cout::nl();
    }
}
