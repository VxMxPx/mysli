<?php

namespace mysli\dev\testme\root\script; class test
{
    const __use = '
        .{ test -> lib.test }
        mysli.toolkit.cli.{ prog, param, ui, output, util }
        mysli.toolkit.{ fs.dir, fs.file, fs.fs -> fs, pkg, type.arr -> arr }
    ';

    /**
     * Run testing utility.
     * --
     * @param array $args
     * --
     * @return boolean
     */
    static function __run(array $args)
    {
        /*
        Set params.
         */
        $prog = new prog('Mysli Testing Utility', '', 'mysli.dev.testme.test');
        $prog
        ->create_parameter('PACKAGE', [
            'required' => true,
            'help'     => 'Package to be tested, in format: '.
                            '`vendor.package.class::method.filter`. '.
                            'Only `vendor.package` are required segments, '.
                            'use the rest to narrow down the amount of tests to be run.',
        ])
        ->create_parameter('--watch/-w', [
            'type' => 'boolean',
            'def'  => false,
            'help' => 'Watch package\'s directory and re-run tests when changes occurs.'
        ])
        ->create_parameter('--diff/-d', [
            'type' => 'boolean',
            'def'  => true,
            'help' => 'Print side-by-side comparison of expected/actual '.
                        'results for failed tests.'
        ]);

        if (null !== ($r = prog::validate_and_print($prog, $args)))
            return $r;

        list($package, $watch, $diff) = $prog->get_values(
            'package', '--watch', '--diff'
        );

        if (!$watch)
            return self::test($package, $diff);
        else
            return self::watch($package, $diff);
    }

    /**
     * Test particular package.
     * --
     * @param string  $pid In format: vendor.package.class::method.filter
     * @param boolean $diff
     * --
     * @return boolean
     */
    private static function test($pid, $diff)
    {
        list($path, $package, $filter) = self::ppf($pid);

        if (!$package)
        {
            ui::error("No tests found for: `{$pid}`");
            return false;
        }

        // Get list of tests to run, by providing a package name.
        $testfiles = self::get_tests_by_pid($pid);

        // If there's no tests available, just skip...
        if (empty($testfiles))
        {
            ui::warning("No tests for: `{$package}`.");
            return false;
        }

        /*
        Some general meters...
         */
        $sum_succeeded = 0;
        $sum_skipped   = 0;
        $sum_failed    = 0;
        $sum_all       = 0;
        $sum_time      = 0;

        /*
        Loop through tests...
         */
        foreach ($testfiles as $testfile)
        {
            // Test base filename
            $testfilebase = substr(basename($testfile), 0, -4);

            // Get results a from file
            try
            {
                list($global, $tests) = lib\test::file($testfile);
            }
            catch (\Exception $e)
            {
                ui::error("ERROR:\n".$e->getMessage());
                return false;
            }

            // Loop through results, and generate report.
            foreach ($tests as $testcase)
            {
                // Generate test...
                $test_generated = lib\test::generate($testcase, $global);

                // Run tests...
                $res = lib\test::run($test_generated, $testcase, $global);

                // Update general stats
                $sum_succeeded += ($res['succeed'] === true);
                $sum_skipped   += ($res['skipped'] !== null);
                $sum_failed    += ($res['succeed'] === false);
                $sum_all++;
                $sum_time += $res['runtime'];

                // Succeed, failed, skipped?
                if ($res['succeed'])
                {
                    output::format("<green>SUCCEED:</green> [{$testfilebase}] {$testcase['title']}\n");
                }
                elseif ($res['skipped'])
                {
                    output::format("<yellow>SKIPPED:</yellow> [{$testfilebase}] {$testcase['title']}\n");
                    output::format("         {$testcase['skip']}\n");
                    ui::nl();
                }
                else
                {
                    output::format("<red>FAILED:</red> [{$testfilebase}] {$testcase['title']}\n");
                    output::line("  FILE: {$testfile}");
                    output::line("  LINE: {$testcase['lineof']['test']}");
                    if ($diff)
                    {
                        ui::nl();
                        self::generate_diff($res['expect'], $res['actual']);
                    }
                    ui::nl();
                }
            }
        }

        // Generate nice footer with all stats.
        self::generate_stats(
            $sum_succeeded, $sum_skipped, $sum_failed, $sum_all, $sum_time
        );
        ui::nl();

        // If non failed, then this succeeded, otherwise, failed.
        return $sum_failed === 0;
    }

    /**
     * Run test(s) for particular package, and watch for change.
     * --
     * @param  string  $pid In format: vendor.package.class::method.filter
     * @param  boolean $diff    Weather to print diff for failed tests.
     * --
     * @return boolean
     */
    private static function watch($pid, $diff)
    {
        list($path, $package, $filter) = self::ppf($pid);

        // Check if package // Dir exists...
        if (!$package || !fs\dir::exists($path))
            return false;

        // Loop will actually re-run this script multiple times (calling system),
        // so -w / --watch needs to be removed to avoid infinite loops...
        $arguments = $_SERVER['argv'];
        if (false !== ($k = array_search('-w', $arguments)))
            unset($arguments[$k]);
        if (false !== ($k = array_search('--watch', $arguments)))
            unset($arguments[$k]);

        // Wait for changes
        fs\file::observe(fs::binpath($package), function ($changes) use ($pid, $diff, $arguments)
        {
            // Re-run tests...
            // self::test($pid, $diff);

            // Call self over and over again
            // This is done in such way, so that changes in PHP files are
            // registered. Each run is fresh...
            system(implode(" ", $arguments));

        }, "*.php|{$filter}", true, 2, true);
    }

    /**
     * Get all tests using package's full ID/name.
     * --
     * @param string $pid vendor.package.class::method.filter
     * --
     * @return array
     *         [/full/absolute/test/path/file.phpt, ...]
     */
    private static function get_tests_by_pid($pid)
    {
        list($path, $_, $filter) = self::ppf($pid);

        // Is actual directory there...
        if (!fs\dir::exists($path))
        {
            ui::warning("Path not found: `{$path}`");
            return false;
        }

        // Find tests
        return fs\file::find($path, $filter);
    }

    /**
     * Return path, package, filter.
     * --
     * @param  string $pid vendor.package.sub.class::method.filter
     * --
     * @return array
     *         [ string $path, string $package, string $filter ]
     */
    private static function ppf($pid)
    {
        // Get Method/Filter
        $pids = explode('::', $pid, 2);
        $pidroot = $pids[0];
        if (isset($pids[1]))
        {
            $method_filter = explode('.', $pids[1]);
            $method = $method_filter[0];
            if (isset($method_filter[1]))
                $filter = $method_filter[1];
            else
                $filter = null;

            $filter = $method.($filter ? ".{$filter}" : '*').'.t.php';
        }
        else
        {
            $filter = "*.t.php";
        }

        // Package
        $package = pkg::by_namespace(str_replace('.', '\\', $pidroot));
        $classes = substr($pidroot, strlen($package)+1);
        $classes = str_replace('.', '/', $classes);

        // Actual path to the tests...
        $path = fs::binpath($package, 'tests', $classes);

        return [$path, $package, $filter];
    }

    /**
     * Generate and print diff.
     * --
     * @param  array $expect
     * @param  array $actual
     * --
     * @return void
     */
    private static function generate_diff($expect, $actual)
    {
        $width = util::terminal_width();
        $width = $width < 50 ? $width : 50;

        // Expected
        // output::green("... EXPECT ".str_repeat(".", $width));
        output::light_green("    EXPECT");
        output::green(str_repeat("^", $width+11));
        self::diff_out($expect, $actual, false);

        ui::nl();

        // Actual
        // output::red("... RESULT ".str_repeat(".", $width));
        output::light_red("    RESULT");
        output::red(str_repeat("^", $width+11));
        self::diff_out($actual, $expect, true);
    }

    /**
     * Find actual diff, withing line, compared to expecation.
     * --
     * @param array   $one
     * @param array   $two
     * @param boolean $mark
     * @param boolean $is_array
     * @param integer $level
     * --
     * @return void
     */
    private static function diff_out(
        array $one, array $two, $mark=false, $is_array=false, $level=-1)
    {
        foreach ($one as $id => $line)
        {
            // Transform to array if multiline string
            if (is_string($line) && strpos($line, "\n") !== false)
            {
                $line = explode("\n", $line);

                if (!is_string($two[$id]))
                {
                    $two[$id] = explode("\n", $two[$id]);
                }
                else
                {
                    $two[$id] = [ $two[$id] ];
                }

                self::diff_out($line, $two[$id], $mark, false, $level+1);

                continue;
            }

            // Call self again in case of array....
            if (is_array($line))
            {
                if (!is_array($two[$id]))
                {
                    $two[$id] = [ $two[$id] ];
                }

                self::diff_out($line, $two[$id], $mark, true, $level+1);
                continue;
            }

            // Stringify line...
            $line = self::stringify($line);

            // Output Pre-Indent
            if ($level > 0)
                output::line(str_repeat(' ', $level*4), false);

            if ($is_array)
            {
                // If not set, then print is a missing...
                if (!isset($two[$id]))
                {
                    $two[$id] = '<MISSING>';
                    $linetype = '<MISSING> ';
                    $twotype  = '<MISSING> ';
                }
                else
                {
                    $linetype = '('.gettype($line).') ';
                    $twotype  = '('.gettype($two[$id]).') ';
                }


                if ($linetype === $twotype && $linetype !== '<MISSING> ')
                    $linetype = $twotype = '';

                $line = "{$id} : {$linetype}{$line}";
                $need = "{$id} : {$twotype}{$two[$id]}";
            }
            else
            {
                $need = isset($two[$id]) ? $two[$id] : '<MISSING>';
            }

            // Compare lines...
            if ($mark && ($need !== $line))
                output::red("  > {$line}");
            else
                output::line("    {$line}");
        }
    }

    /**
     * Convert line to presentable string for diff.
     * --
     * @param  mixed $line
     * --
     * @return string
     */
    private static function stringify($line)
    {
        if (is_array($line))
        {
            return arr::readable($line, 4, 4, ': ', "\n", true);
        }
        elseif (is_object($line))
        {
            return get_class($line);
        }
        elseif (is_resource($line))
        {
            return '<RESOURCE>';
        }
        else
        {
            // Strip shell arguments if there...
            if (preg_match('/\\e\[[0-9]+m/', $line))
                $line = escapeshellcmd($line);
            else
                $line = $line;

            return $line;
        }
    }

    /**
     * Generate and print final footer status.
     * --
     * @param integer $succeed
     * @param integer $skipped
     * @param integer $failed
     * @param integer $all
     * @param float   $time
     * --
     * @return void
     */
    private static function generate_stats(
        $succeed, $skipped, $failed, $all, $time)
    {
        $width = util::terminal_width();
        $width = $width < 60 ? $width : 60;

        // Set variables to be printed
        $all     = "RUN: {$all}";
        $failed  = $failed > 0  ? "<red>FAILED: {$failed}</red>"         : "FAILED: 0";
        $succeed = $succeed > 0 ? "<green>SUCCEED: {$succeed}</green>"   : "SUCCEED: 0";
        $skipped = $skipped > 0 ? "<yellow>SKIPPED: {$skipped}</yellow>" : "SKIPPED: 0";
        $time    = "RUN TIME: " . number_format($time, 4);

        ui::line(str_repeat('-', $width));
        output::format("{$all} | {$failed} | {$succeed} | {$skipped} | {$time}\n");
    }
}
