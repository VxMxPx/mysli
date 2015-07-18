<?php

namespace mysli\dev\testme\root\script; class test
{
    const __use = '
        .{ test -> lib.test }
        mysli.toolkit.cli.{ prog, param, ui, output }
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
            'help'     => 'Package to be tested, in format: .'.
                          '`vendor.package.class::method.file`. '.
                          'Only `vendor.package` are required segments, '.
                          'use the rest to narrow down the amount of tests to be run.',
        ])
        ->create_parameter('--watch/-w', [
            'type'    => 'boolean',
            'def'     => false,
            'help'    => 'Watch package\'s directory and re-run tests when changes occurs.'
        ])
        ->create_parameter('--diff/-d', [
            'type'    => 'boolean',
            'def'     => true,
            'help'    => 'Print side-by-side comparison of expected/actual '.
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
     * @param  string  $package In format: vendor.package.class::method.file
     * @param  boolean $diff
     * --
     * @return boolean
     */
    private static function test($package, $diff)
    {
        // Get package's name from full package's ID
        $package_seg  = explode('::', $package, 2);
        $package_name = pkg::by_namespace(str_replace('.', '\\', $package_seg[0]));

        if (!$package_name)
        {
            ui::error("No such package: `{$package_seg[0]}`");
            return false;
        }

        // Get list of tests to run, by providing a package name.
        $testfiles = self::get_tests_by_package($package);

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
            // Get results a from file
            $results = lib\test::file($testfile);

            // Loop through results, and generate report.
            foreach ($results as $case)
            {
                // Update general stats
                $sum_succeeded += (int) $case['succeed'];
                $sum_skipped   += $case['skipped'] !== null;
                $sum_failed    += (int) $case['failed'];
                $sum_all++;
                $sum_time += $case['runtime'];

                // Succeed, failed, skipped?
                if ($case['succeed'])
                {
                    output::format(
                        "<green>!!</green> {$case['name']} <right>OK</right>"
                    );
                }
                elseif ($case[$skipped] !== null)
                {
                    output::format(
                        "<yellow>!!</yellow> {$case['name']} <right>SKIPPED</right>"
                    );
                    output::format(
                        "<yellow>..</yellow> Reason: {$case['skipped']}"
                    );
                    ui::nl();
                }
                elseif ($case[$failed])
                {
                    output::format(
                        "<red>!!</red> {$case['name']} <right>FAILED</right>"
                    );
                    if ($diff)
                        self::generate_diff($case['expected'], $case['result']);
                    ui::nl();
                }
                else
                {
                    output::format(
                        "<red>??</red> {$case['name']} <right>UNKNOWN</right>"
                    );
                }
            }
        }

        // Generate nice footer with all stats.
        self::generate_stats(
            $sum_succeeded, $sum_skipped, $sum_failed, $sum_all, $sum_time
        );

        // If non failed, then this succeeded, otherwise, failed.
        return $sum_failed === 0;
    }

    /**
     * Run test(s) for particular package, and watch for change.
     * --
     * @param  string  $package In format: vendor.package.class::method.file
     * @param  boolean $diff    Weather to print diff for failed tests.
     * --
     * @return boolean
     */
    private static function watch($package, $diff)
    {
        /*
        Extract file, if indeed exists.
        So: vendor.package.class::method.file <-- we're interested in file here
        If file was specified, only that test file will be observed for changes.
         */
        $package_seg = explode('::', $package, 2);

        // Check if file was specified and extract it...
        $test_file =  (isset($package_seg[1]) && strpos($package_seg[1], '.'))
            ? substr($package, strrpos($package, '.'))
            : '*';

        // Wait for changes
        file::observe($dir, function ($changes) use ($package, $diff)
        {
            // Re-run tests...
            self::test($package, $diff);

        }, "*.php|{$test_file}.t.php", 2);
    }

    /**
     * Get all tests using package's full ID/name.
     * --
     * @param  string $package vendor.package.class::method.file
     * --
     * @return array
     *         [/full/absolute/test/path/file.phpt, ...]
     */
    private static function get_tests_by_package($package)
    {

    }

    /**
     * Generate and print diff.
     * --
     * @param  array $expected
     * @param  array $result
     * --
     * @return void
     */
    private static function generate_diff($expected, $result)
    {

    }

    /**
     * Generate and print final footer status.
     * --
     * @param integer $succeeded
     * @param integer $skipped
     * @param integer $failed
     * @param integer $all
     * @param float   $time
     * --
     * @return void
     */
    private static function generate_Stats(
        $succeeded, $skipped, $failed, $all, $time)
    {

    }
}
