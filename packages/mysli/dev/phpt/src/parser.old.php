<?php

namespace mysli\dev\phpt {

    __use(__namespace__, [
            'mysli/framework' => [
                'type/{str,arr}',
                'fs/{fs,file,dir}',
                'exception/*' => 'framework/exception/%s'
            ]
        ]
    );

    class parser {

        private static $ini_overwrites = [
            'output_handler'                 => '',
            'open_basedir'                   => '',
            'safe_mode'                      => '0',
            'disable_functions'              => '',
            'output_buffering'               => 'Off',
            'error_reporting'                => (E_ALL | E_STRICT),
            'display_errors'                 => '1',
            'display_startup_errors'         => '1',
            'log_errors'                     => '0',
            'html_errors'                    => '0',
            'track_errors'                   => '1',
            'report_memleaks'                => '1',
            'report_zend_debug'              => '0',
            'docref_root'                    => '',
            'docref_ext'                     => '.html',
            'error_prepend_string'           => '',
            'error_append_string'            => '',
            'auto_prepend_file'              => '',
            'auto_append_file'               => '',
            'magic_quotes_runtime'           => '0',
            'ignore_repeated_errors'         => '0',
            'precision'                      => '14',
            'memory_limit'                   => '128M',
            'opcache.fast_shutdown'          => '0',
            'opcache.file_update_protection' => '0',
        ];


        /**
         * Process PHPT string
         * @param  string $phpt
         * @param  string $id   unique ID for this file
         * @return array
         */
        static function process($phpt, $id=null) {
            // Skipf
            // if (isset($segments['skipf'])) {
            //     $skip = !!trim(
            //         self::run_test($id.'_skip', $segments['skipf'], $segments));
            // } else {
            //     $skip = false;
            // }
            // Test
            if (!$skip) {
                $st  = microtime(true);
                $out = self::run_test($id, $segments[$file], $segments, $file);
                $et = microtime(true);
                $status = self::compare(
                    $id, $out, $segments[$expect], $expect);
            } else {
                $status = 2;
            }

            return [
                'status'   => $status,
                'skip'     => $skip,
                'expect'   => $segments[$expect],
                'output'   => $out,
                'diff'     => null,
                'time'     => ($et - $st),
            ];
        }

        /**
         * Execute one test
         * @param  string $filename
         * @param  string $file  (file content, or filename if include)
         * @param  array  $params
         * @param  strinh $file_type
         * @return string
         */
        private static function run_test($filename, $file, $params,
                                         $file_type=null) {
            $php = exec('which php') ?: 'php';

            if ($file_type === 'file_external') {
                $file = self::get_ext_file_content($filename, $file);
            }



            $input_fn = "{$filename}.input";

            if (!extension_loaded("zlib")
                && array_key_exists("gzip_post", $params
                || array_key_exists("deflate_post", $params))) {
                throw new framework\exception\base(
                    "For `gzip_post` or `deflate_post` you need to enable ".
                    "`zlib` extension.", 1);
            }



            $ini_settings = array_merge(self::$ini_overwrites, $ini_settings);
            $ini_settings = self::ini_to_params($ini_settings);

            // file_put_contents($input_fn, $post, FILE_BINARY);
            // $opt = " < \"{$input_fn}\"";


            $tempdir = fs::datpath('temp/phpt');
            $filename = fs::ds($tempdir, md5($filename));
            $file = self::get_header_code() . $file;
            file::write("{$filename}.php", $file);




            // > \"{$filename}.out\"
            $command = implode(' ', [
                $php, $ini_settings, '-f "' . $filename . '.php"',
                $args, '2>&1', $opt]);

            $output = self::exec(
                $command,
                $env, dirname($filename),
                isset($params['stdin']) ? $params['stdin'] : null);

            // $output = file::read("{$filename}.out");
                        // file::remove("{$filename}.php");
            // file::remove("{$filename}.out");

            if ($file_type === 'fileeof') {
                $output = preg_replace("/[\r\n]+$/", '', $output);
            }

            return $output;
        }
        /**
         * Get header <?php ?> which will include and run core.
         * @return string
         */
        private static function get_header_code() {
            $headers   = [];
            $headers[] = '<?php';
            $headers[] = sprintf(
                'include("%s");',
                fs::pkgpath(MYSLI_CORE, 'src/__init.php'));
            $headers[] = sprintf(
                'call_user_func("%s", "%s", "%s");',
                str_replace('/', '\\\\', MYSLI_CORE).'\\\\__init',
                fs::datpath(),
                fs::pkgpath());
            $headers[] = '?>';
            return implode("\n", $headers);
        }
        /**
         * Compare output with expected.
         * @param  string $filename
         * @param  string $out
         * @param  string $expect
         * @param  string $expect_type
         * @return boolean
         */
        private static function compare($filename, $out, $expect, $expect_type) {
            if (substr($expect_type, -9) === '_external') {
                $expect_type = substr($expect_type, 0, -9);
                $expect = self::get_ext_file_content($filename, $expect);
            }
            if ($expect_type === 'expectf') {
                $expect = self::process_expectf($expect);
            }
            $expect = trim($expect);
            $out    = trim($out);
            if ($expect_type === 'expectf' || $expect_type === 'expectregex') {
                return preg_match("/^{$expect}\$/s", $out);
            } else {
                return strcmp($out, $expect) === 0;
            }
        }
        /**
         * Process expectf
         *   Copyright (c) 1997-2010 The PHP Group
         *   This function is subject to version 3.01 of the PHP license,
         *   that is bundled with this package in the file LICENSE, and is
         *   available through the world-wide-web at the following url:
         *   http://www.php.net/license/3_01.txt
         *   If you did not receive a copy of the PHP license and are unable to
         *   obtain it through the world-wide-web, please send a note to
         *   license@php.net so we can mail you a copy immediately.
         * @param  string $expect
         * @return string
         */
        private static function process_expectf($expect) {
            // do preg_quote, but miss out any %r delimited sections
            $temp = "";
            $r    = "%r";
            $start_offset = 0;
            $length       = strlen($expect);

            while($start_offset < $length) {
                $start = strpos($expect, $r, $start_offset);
                if ($start !== false) {
                    // we have found a start tag
                    $end = strpos($expect, $r, $start+2);
                    if ($end === false) {
                        // unbalanced tag, ignore it.
                        $end = $start = $length;
                    }
                } else {
                    // no more %r sections
                    $start = $end = $length;
                }
                // quote a non re portion of the string
                $temp = $temp . preg_quote(
                    substr(
                        $expect, $start_offset, ($start - $start_offset)), '/');
                // add the re unquoted.
                if ($end > $start) {
                    $temp = $temp .
                        '(' . substr($expect, $start+2, ($end - $start-2)). ')';
                }
                $start_offset = $end + 2;
            }
            $expect = $temp;

            $expect = str_replace(
                array('%binary_string_optional%'),
                'string',
                $expect
            );
            $expect = str_replace(
                array('%unicode_string_optional%'),
                'string',
                $expect
            );
            $expect = str_replace(
                array('%unicode\|string%', '%string\|unicode%'),
                'string',
                $expect
            );
            $expect = str_replace(
                array('%u\|b%', '%b\|u%'),
                '',
                $expect
            );
            // Stick to basics
            $expect = str_replace('%e', '\\' . DIRECTORY_SEPARATOR, $expect);
            $expect = str_replace('%s', '[^\r\n]+', $expect);
            $expect = str_replace('%S', '[^\r\n]*', $expect);
            $expect = str_replace('%a', '.+', $expect);
            $expect = str_replace('%A', '.*', $expect);
            $expect = str_replace('%w', '\s*', $expect);
            $expect = str_replace('%i', '[+-]?\d+', $expect);
            $expect = str_replace('%d', '\d+', $expect);
            $expect = str_replace('%x', '[0-9a-fA-F]+', $expect);
            $expect = str_replace(
                '%f', '[+-]?\.?\d+\.?\d*(?:[Ee][+-]?\d+)?', $expect);
            $expect = str_replace('%c', '.', $expect);
            // %f allows two points "-.0.0"
            // but that is the best *simple* expression
            return $expect;
        }

        /**
         * Convert ini array to PHP params (string)
         * @param  array $ini
         * @return string
         */
        private static function ini_to_params($ini) {
            $output = '';

            foreach($ini as $name => $value) {
                if (is_array($value)) {
                    foreach($value as $val) {
                        $val = addslashes($val);
                        $output .= " -d \"$name=$val\"";
                    }
                } else {
                    if (substr(PHP_OS, 0, 3) == "WIN"
                        && !empty($value) && $value{0} == '"') {
                        $len = strlen($value);
                        if ($value{$len - 1} == '"') {
                            $value{0} = "'";
                            $value{$len - 1} = "'";
                        }
                    } else {
                        $value = addslashes($value);
                    }
                    $output .= " -d \"$name=$value\"";
                }
            }

            return trim($output);
        }
        /**
         * Execute PHP command
         * @param  string $command
         * @param  mixed  $env
         * @param  string $cwd
         * @param  string $stdin
         * @return string
         */
        private static function exec($command, $env, $cwd, $stdin=null) {

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
        /**
         * Get external file contents.
         * @param  string $filename
         * @param  string $file
         * @return string
         */
        private function get_ext_file_content($filename, $file) {
            $filename = fs::ds(file::name($filename), $file);
            if (!file::exists($filename)) {
                throw new framework\exception\not_found(
                    "External file not found: `{$filename}`", 3);
            } else {
                return file::read($filename);
            }
        }
    }
}
