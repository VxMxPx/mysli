<?php

namespace mysli\dev\test; class test
{
    const __use = '
        mysli.toolkit.{
            fs.fs -> fs,
            fs.file -> file,
            type.str -> str,
            log
        }
        .{ assert, exception.test }
    ';

    /**
     * Test particular file.
     * --
     * @param string $filename Full absolute path.
     * --
     * @throws mysli\dev\test\exception\test 10 File not found.
     * --
     * @return array
     *         [ array $global, array $tests, string $namespace ]
     */
    static function file($filename)
    {
        if (!file::exists($filename))
            throw new exception\test("File not found: `{$filename}`.", 10);

        log::debug("About to test file: `{$filename}`", __CLASS__);

        try
        {
            $processed   = static::process(file::read($filename));
            $processed[] = static::get_namespace($filename);

            return $processed;
        }
        catch (\Exception $e)
        {
            throw new exception\test($e->getMessage()."\nIn file: {$filename}");
        }
    }

    /**
     * Process test file's string.
     * --
     * @param  string $string
     * --
     * @throws mysli\dev\test\exception\test 10 Invalid tag.
     * @throws mysli\dev\test\exception\test 11 Unexpected tag `Description`.
     * @throws mysli\dev\test\exception\test 12 Unexpected tag `Expect`.
     * @throws mysli\dev\test\exception\test 13 (@see static::resolve_expect())
     * @throws mysli\dev\test\exception\test 14 Unexpected tag `Skip`.
     * @throws mysli\dev\test\exception\test 15 Unexpected tag `Use`.
     * @throws mysli\dev\test\exception\test 20 Invalid buffer.
     * --
     * @return array [ array $global, array $tests ]
     */
    static function process($string)
    {
        $string = str::to_unix_line_endings($string);
        $lines = explode("\n", $string);

        // Specific to the file (before, after, define)
        $global = [
            'before' => [],
            'after'  => [],
        ];

        // All tests found so far
        $tests = [];

        // Current target
        $target = null;
        // Buffer Location:
        //  test|skip|expect
        //  before:|after:
        //  define:target
        $buffer = null;

        foreach ($lines as $lineno => $line)
        {
            if ($buffer !== 'expect' &&
                preg_match('/^#: ([a-z]+) ?(.*?)$/i', $line, $match))
            {
                log::debug("Found: `{$match[1]}` `{$match[2]}`", __CLASS__);

                $tag    = strtolower($match[1]);
                $option = isset($match[2]) ? $match[2] : null;

                switch ($tag)
                {
                    case 'test':
                        // Unset any previously set target
                        unset($target);

                        // Define new target
                        $target = [
                            'title'       => $option,
                            'description' => '',
                            'expect'      => ['assertion'],
                            'skip'        => null,
                            'use'         => [],
                            'lineof'      => [
                                'test'    => null,
                                'skip'    => null,
                                'expect'  => null,
                            ],
                            'code'        => [
                                'test'    => [],
                                'skip'    => [],
                                'expect'  => []
                            ]
                        ];
                        // Append target
                        $tests[] = &$target;
                        $buffer  = 'test';
                    continue;

                    case 'expect':
                        if (!is_array($target))
                            throw new exception\test(
                                f_error($lines, $lineno, "Unexpected tag: `Expect`."),
                                12
                            );
                        else
                        {
                            try
                            {
                                $target['expect'] = static::resolve_expect($option);
                                $buffer = 'test';
                            }
                            catch (\Exception $e)
                            {
                                throw new exception\test(
                                    f_error($lines, $lineno, $e->getMessage()),
                                    13
                                );
                            }
                        }
                    continue;

                    case 'skip':
                        if (!is_array($target))
                            throw new exception\test(
                                f_error($lines, $lineno, "Unexpected tag: `Skip`."),
                                14
                            );
                        else
                        {
                            $target['skip'] = trim($option);
                            $buffer = 'skip';
                        }
                    continue;

                    case 'use':
                        if (!is_array($target))
                            throw new exception\test(
                                f_error($lines, $lineno, "Unexpected tag: `Use`."),
                                15
                            );
                        else
                        {
                            $option = strtolower($option);
                            $opts = explode(' ', $option);
                            $target['use'][] = [
                                trim($opts[0]),
                                isset($opts[1]) ? trim($opts[1]) : 'before'
                            ];
                        }
                    continue;

                    case 'before':
                    case 'after':
                    case 'define':
                        unset($target);
                        $option = trim(strtolower($option));
                        $buffer = $tag.($option ? ':'.$option : '');
                        if (!isset($global[$buffer]))
                            $global[$buffer] = [];
                    continue;

                    case 'description':
                        if (!is_array($target))
                            throw new exception\test(
                                f_error($lines, $lineno, "Unexpected tag: `Description`."),
                                11
                            );
                        else
                            $target['description'] = $option;
                    continue;

                    default:
                        throw new exception\test(
                            f_error($lines, $lineno, "Invalid tag: `{$tag}`."),
                            10
                        );
                }
            }
            else
            {
                // No buffer, skip...
                if (!$buffer)
                {
                    log::debug(
                        "No buffer is waiting for `{$line}` at `{$lineno}`.",
                        __CLASS__
                    );
                    continue;
                }
                else
                {
                    // Is new output being opened ....
                    if ($buffer === 'test'
                        && $target['expect'][0] === 'output'
                        && substr($line, 0, strlen($target['expect'][1])) === $target['expect'][1])
                    {
                        $buffer = 'expect';
                    }
                    // Buffering `expect`
                    elseif ($buffer === 'expect')
                    {
                        $close = substr($target['expect'][1], 3);
                        $close = trim($close, '\'"').';';

                        if (trim($line) === $close)
                        {
                            $buffer = 'test';
                            continue;
                        }
                        else
                        {
                            $target['code'][$buffer][] = $line;
                        }
                    }
                    // Buffer test, skip or expect..
                    elseif (in_array($buffer, ['test', 'skip', 'expect']))
                    {
                        if ($target['lineof'][$buffer] === null)
                            $target['lineof'][$buffer] = $lineno+1;

                        $target['code'][$buffer][] = $line;
                        continue;
                    }
                    // Buffer anything else
                    elseif (isset($global[$buffer]))
                    {
                        $global[$buffer][] = $line;
                    }
                    else
                    {
                        throw new exception\test(
                            f_error($lines, $lineno, "Invalid buffer: `{$buffer}`."),
                            20
                        );
                    }
                }
            }
        }

        unset($target);

        return [ $global, $tests ];
    }

    /**
     * Generate actual test's code, to be eval.
     * --
     * @param array  $test
     * @param array  $global
     * @param string $namespace
     * --
     * @return string
     */
    static function generate(array $test, array $global, $namespace)
    {
        /*
        Define code, and append namespace + use.
         */
        $code = [];
        $code[] = "namespace {$namespace};";
        $code[] = "use \\mysli\\dev\\test\\assert;";

        /*
        Add lines to be run before each test
         */
        if (!empty($global['before']))
            $code[] = trim(implode("\n", $global['before']));

        /*
        Define Test Function
         */
        $code[] = "\$mysli_test_test_case = function ()\n{";

        /*
        Add line to be run before the test, in case of USE
         */
        foreach ($test['use'] as $use)
        {
            list($use, $when) = $use;
            if (isset($global['define:'.$use]) && $when === 'before')
                $code = array_merge($code, $global['define:'.$use]);
        }

        // Process and append body
        $body = implode("\n", $test['code']['test']);
        $body = trim($body);
        $code[] = $body;
        // End function
        $code[] = "};";

        /*
        Execute function
         */
        $code[] = "\$mysli_test_test_result = \$mysli_test_test_case();";

        /*
        Add line to be run after the test, in case of USE
         */
        foreach ($test['use'] as $use)
        {
            list($use, $when) = $use;
            if (isset($global['define:'.$use]) && $when === 'after')
                $code = array_merge($code, $global['define:'.$use]);
        }

        /*
        Add lines to be run after each test
         */
        if (!empty($global['after']))
            $code[] = trim(implode("\n", $global['after']));

        /*
        Add return statement.
         */
        $code[] = "return \$mysli_test_test_result;";

        return implode("\n", $code);
    }

    /**
     * Run particular test.
     * --
     * @param string $test_code
     * @param array  $test
     * @param array  $global
     * --
     * @return array
     */
    static function run($test_code, array $test, array $global)
    {
        // Append defaults
        $r['succeed'] = null;
        $r['actual']  = [];

        $timestart = microtime(true);

        // Check weather there's a skip test
        if ($test['skip'] && !empty($test['code']['skip']))
        {
            $result = eval(implode("\n", $test['code']['skip']));
            if (!$result)
            {
                $r['skipped'] = true;
                $r['runtime'] = (microtime(true) - $timestart);
                return $r;
            }
        }

        // Run the actual test ...
        try
        {
            set_error_handler(['\\mysli\\dev\\test\\test', 'error_handler']);
            ob_start();
            $result = eval($test_code);
            $output = ob_get_contents();
        }
        catch (\Exception $e)
        {
            $r = static::assert_statement($e, $test['expect']);
            $r['skipped'] = null;
            $r['runtime'] = (microtime(true) - $timestart);
            return $r;
        }
        finally
        {
            restore_error_handler();
            ob_end_clean();
        }

        // Was output expected?
        if ($test['expect'][0] === 'output')
        {
            $test['expect'][1] = implode("\n", $test['code']['expect']);
            $r = static::assert_statement($output, $test['expect']);
        }
        elseif ($test['expect'][0] === 'assertion')
        {
            if (is_array($result) && isset($result['succeed']))
            {
                $r = $result;
            }
            else
            {
                $r = static::assert_statement($result, $test['expect']);
            }
        }
        else
        {
            $r = static::assert_statement($result, $test['expect']);
        }

        $r['skipped'] = null;
        $r['runtime'] = (microtime(true) - $timestart);

        return $r;
    }


    /*
    --- Private ----------------------------------------------------------------
     */

    /**
     * Get test's namespace from filename.
     * --
     * @param string $filename
     * --
     * @return string
     */
    private static function get_namespace($filename)
    {
        $class = substr($filename, strlen(fs::binpath()));
        // toolkit.mysli/tests/router/add.t.php
        $class = trim($class, '\\/');
        // toolkit.mysli/tests/router
        $class = dirname($class);
        // toolkit.mysli/root/tests/router
        $class = substr_replace($class, 'root/', strpos($class, '/')+1, 0);
        // toolkit\mysli\root\tests\router
        $class = str_replace(['/', '.'], '\\', $class);

        return $class;
    }

    /**
     * Resolve `expect` statement and return an array.
     * --
     * @param string $option
     * --
     * @throws mysli\dev\test\exception\test 10 Invalid type.
     * --
     * @return array
     */
    private static function resolve_expect($option)
    {
        $options = explode(' ', $option, 2);
        $type    = strtolower($options[0]);
        $value   = isset($options[1]) ? $options[1] : null;

        switch ($type)
        {
            case 'string':
                return ['string', trim($value, '"')];

            case 'match':
                $value = trim($value, '"');
                $value = preg_quote($value);
                $value = str_replace('\\*', '.*?', $value);
                return ['match', $value];

            case 'integer':
                return ['integer', (int) trim($value)];

            case 'float':
                return ['float', (float) trim($value)];

            case 'true':
                return ['boolean', true];

            case 'false':
                return ['boolean', false];

            case 'null':
                return ['null', null];

            case 'exception':
                // Full format would be: vendor\package\exception\type code message
                // But the code and message are optional.
                $values = explode(' ', $value, 3);
                $exception_type = trim($values[0]);
                $exception_code = null;
                $exception_message = [];

                // Check if we have code (or message)
                if (isset($values[1]))
                {
                    if (is_numeric($values[1]))
                        $exception_code = (int) $values[1];
                    else
                        $exception_message[] = $values[1];
                }

                // Continuation of messages
                if (isset($values[2]))
                    $exception_message[] = $values[2];

                // Assemble message
                if (!empty($exception_message))
                {
                    $exception_message = implode(' ', $exception_message);
                    $exception_message = trim($exception_message, '"');
                    $exception_message = preg_quote($exception_message);
                    $exception_message = str_replace('\\*', '.*?', $exception_message);
                    $exception_message = "/^{$exception_message}$/";
                }
                else
                    $exception_message = null;

                return [
                    'exception',
                    $exception_type,
                    $exception_code,
                    $exception_message
                ];

            case 'instance':
                return ['instance', trim($value)];

            case 'output':
                return ['output', trim($value)];

            default:
                throw new exception\file("Invalid type: `{$type}`", 10);
        }
    }


    /**
     * Match an array with description of expectation with an actual value.
     * --
     * @param  mixed $actual
     * @param  array $expect
     * --
     * @throws mysli\dev\test\exception\test 10 Unknown type.
     * --
     * @return array
     */
    static function assert_statement($actual, array $expect)
    {
        // Set fail safes
        if (!isset($expect[1])) $expect[1] = null;
        if (!isset($expect[2])) $expect[2] = null;
        if (!isset($expect[3])) $expect[3] = null;

        list($type, $value, $code, $message) = $expect;

        switch ($type)
        {
            case 'output':
            case 'string':
                return assert::equals($actual, (string) $value);

            case 'match':
                return assert::match($actual, (string) $value);

            case 'integer':
                return assert::equals($actual, (integer) $value);

            case 'float':
                return assert::equals($actual, (float) $value);

            case 'boolean':
                return assert::equals($actual, (boolean) $value);

            case 'null':
                return assert::equals($actual, null);

            case 'instance':
                return assert::instance($actual, $value);

            case 'exception':
                $r = [];
                $r['succeed'] = false;

                $r['actual'] = assert::describe($actual);
                $r['expect'] = $expect;

                // Must be object and exception
                if (!is_object($actual) || !is_a($actual, '\\Exception'))
                    return $r;

                // Must be an instance of particular exception
                if (!is_a($actual, $value))
                    return $r;

                // If there's an expectation of code, check it now
                if ($code && $code !== $actual->getCode())
                    return $r;

                // If there's an expectation of message, check it now
                if ($message && !preg_match($message, $actual->getMessage()))
                    return $r;

                $r['succeed'] = true;
                return $r;

            case 'assertion':
                $r = [];
                $r['succeed'] = false;
                $r['actual']  = assert::describe($actual);
                $r['expect']  = $expect;
                return $r;

            default:
                throw new exception\test("Unknown type: `{$type}`.", 10);
        }
    }

    /**
     * All errors to exception for nice output.
     * --
     * @param  integer $number
     * @param  string  $message
     * @param  string  $file
     * @param  integer $line
     * @param  array   $context
     */
    static function error_handler($severity, $message, $file, $line, $context)
    {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
}
