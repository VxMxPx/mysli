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
namespace mysli\dev\phpt;

__use(__namespace__, '
    mysli.framework.fs/fs,file
    mysli.framework.type/str,arr
    mysli.framework.exception/*  AS  framework\exception\*
');

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
     * Process PHPT file and return an array with segments.
     * @param  string $phpt_file
     * @return array
     */
    static function process($phpt_file) {
        $tphpt = [];
        $phpt  = file::read($phpt_file);
        $phpt  = str::to_unix_line_endings($phpt);
        // $phpt  = preg_split(
        //     '/^--([A-Z\\_]+)--$\\s/ms',
        //     $phpt, null);
        $phpt .= "\n--EOF--";
        preg_match_all(
            '/^--([A-Z]+)(?: \\((.*?)\\))?--(.*?)(?=^--[A-Z]+)/sm',
            $phpt, $phptr, PREG_SET_ORDER);
        $phpt = [];

        foreach ($phptr as $matches) {
            if (!isset($matches[1])) { continue; }
            if ($matches[1] === 'VIRTUAL') {
                $phpt['virtual'][] = [
                    'file'     => $matches[2],
                    'contents' => trim($matches[3])
                ];
            } else {
                $phpt[strtolower($matches[1])] = trim($matches[3]);
            }
        }

        if (!arr::key_in($phpt, 'test')) {
            throw new framework\exception\data(
                "Field `--TEST--` is missing in file", 10);
        }
        if (!($file = self::get_first_key($phpt,
            ['file', 'fileeof', 'file_external']))) {
            throw new framework\exception\data(
                "One of following fields is required: ".
                "`--FILE--`, `--FILEEOF--`, `--FILE_EXTERNAL--`", 11);
        }
        if (!($expect = self::get_first_key($phpt,
            ['expect', 'expectf', 'expectregex', 'expect_external',
            'expectf_external', 'expectregex_external']))) {
            throw new framework\exception\argument(
                "One of following fields is required: ".
                "`--EXPECT--`, `--EXPECTF--`, `--EXPECTREGEX--`, ".
                "`--EXPECT_EXTERNAL--`, `--EXPECTF_EXTERNAL--`, ".
                "`--EXPECTREGEX_EXTERNAL--`", 12);
        }
        $phpt['file_type']   = trim($file);
        $phpt['expect_type'] = trim($expect);
        $phpt['test']        = trim($phpt[$file]);
        $phpt['expect']      = trim($phpt[$expect]);
        if ($expect !== 'expect') { unset($phpt[$expect]); }
        unset($phpt['file']);

        if (!extension_loaded("zlib")
            && array_key_exists("gzip_post", $phpt
            || array_key_exists("deflate_post", $phpt))) {
            throw new framework\exception\base(
                "For `gzip_post` or `deflate_post` you need to enable ".
                "`zlib` extension.", 1);
        }

        // Get external content
        if ($phpt['file_type'] === 'file_external') {
            $phpt['file_type'] = 'file';
            $phpt['test'] = self::get_external_content(
                dirname($phpt_file), $phpt['test']);
        }
        if (substr($phpt['expect_type'], -9) === '_external') {
            $phpt['expect_type'] = substr($phpt['expect_type'], 0, -9);
            $phpt['expect'] = self::get_external_content(
                dirname($phpt_file), $phpt['expect']);
        }

        $phpt['expect_raw'] = $phpt['expect'];
        if ($phpt['expect_type'] === 'expectf') {
            $phpt['expect_type'] = 'expectregex';
            $phpt['expect'] = self::process_expectf($phpt['expect']);
        }

        $phpt['cgi'] = false;

        $env['REQUEST_METHOD']  = 'GET';
        $env['CONTENT_TYPE']    = '';
        $env['CONTENT_LENGTH']  = '';
        $env['REDIRECT_STATUS'] = '1';
        $env['PATH_TRANSLATED'] = dirname($phpt_file);
        $env['SCRIPT_FILENAME'] = basename($phpt_file);
        if (isset($phpt['get'])) {
            $phpt['cgi'] = true;
            $env['QUERY_STRING'] = trim($phpt['get']);
        } else {
            $env['QUERY_STRING'] = '';
        }
        if (isset($phpt['cookie'])) {
            $phpt['cgi'] = true;
            $env['HTTP_COOKIE'] = trim($phpt['cookie']);
        } else {
            $env['HTTP_COOKIE'] = '';
        }
        // Inputs
        if (isset($phpt['post'])) {
            $phpt['cgi']    = true;
            $phpt['post']   = trim($phpt['post']);
            $phpt['inputf'] = 'post';
            $env['REQUEST_METHOD'] = 'POST';
            $env['CONTENT_TYPE']   = 'application/x-www-form-urlencoded';
            $env['CONTENT_LENGTH'] = strlen($phpt['post']);
        } elseif (isset($phpt['deflate_post'])) {
            $phpt['cgi']          = true;
            $phpt['deflate_post'] = trim($phpt['deflate_post']);
            $phpt['deflate_post'] = gzcompress($phpt['deflate_post'], 9);
            $phpt['inputf']       = 'deflate_post';
            $env['HTTP_CONTENT_ENCODING'] = 'deflate';
            $env['REQUEST_METHOD']    = 'POST';
            $env['CONTENT_TYPE']      = 'application/x-www-form-urlencoded';
            $env['CONTENT_LENGTH']    = strlen($phpt['deflate_post']);
        } elseif (isset($phpt['gzip_post'])) {
            $phpt['cgi']       = true;
            $phpt['gzip_post'] = trim($phpt['gzip_post']);
            $phpt['gzip_post'] = gzencode($phpt['gzip_post'], 9,FORCE_GZIP);
            $phpt['inputf']    = 'gzip_post';
            $env['HTTP_CONTENT_ENCODING'] = 'gzip';
            $env['REQUEST_METHOD'] = 'POST';
            $env['CONTENT_TYPE']   = 'application/x-www-form-urlencoded';
            $env['CONTENT_LENGTH'] = strlen($phpt['gzip_post']);
        } elseif (isset($phpt['put']) || isset($phpt['post_raw'])) {
            $phpt['cgi'] = true;
            $post = trim($phpt[(isset($phpt['put']) ? 'put' : 'post_raw')]);
            $phpt['request'] = '';
            $phpt['inputf']  = 'request';
            $started = false;

            foreach (explode("\n", $post) as $line) {
                if (empty($env['CONTENT_TYPE'])
                    && preg_match('/^Content-Type:(.*)/i', $line, $res)) {
                    $env['CONTENT_TYPE'] =
                        trim(str_replace("\r", '', $res[1]));
                    continue;
                }
                if ($started) {
                    $phpt['request'] .= "\n";
                }
                $started = true;
                $phpt['request'] .= $line;
            }

            $env['CONTENT_LENGTH'] = strlen($phpt['request']);
            $env['REQUEST_METHOD'] = isset($phpt['put']) ? 'PUT' : 'POST';

            if (empty($phpt['request'])) {
                throw new framework\exception\data(
                    "Invalid `{$env['REQUEST_METHOD']}` request.", 1);
            }
        }

        // Special env settings
        if (!empty($phpt['env'])) {
            foreach(explode("\n", trim($phpt['env'])) as $e) {
                $e = explode('=', trim($e), 2);
                if (!empty($e[0]) && isset($e[1])) {
                    $env[$e[0]] = $e[1];
                }
            }
        }

        $phpt['env'] = $env;

        // Special ini settings
        if (isset($phpt['ini'])) {
            if (strpos($phpt['ini'], '{PWD}') !== false) {
                $phpt['ini'] = str_replace(
                    '{PWD}', dirname($phpt_file), $phpt['ini']);
            }
            $phpt['ini'] = self::ini_to_array(
                preg_split( "/[\n\r]+/", $phpt['ini']));
        } else $phpt['ini'] = [];
        $phpt['ini'] = array_merge(self::$ini_overwrites, $phpt['ini']);
        $phpt['inip'] = self::ini_to_params($phpt['ini']);

        // Special args
        $phpt['args'] = isset($phpt['args']) ? ' -- ' . $phpt['args'] : '';
        $phpt['load'] = self::mk_loader();

        // Process imports
        if (isset($phpt['import'])) {
            $imports = $phpt['import'];
            $imports = explode("\n", $imports);
            $phpt['import'] = "<?php\n\$import = [];\n";
            foreach ($imports as $import) {
                if (strpos($import, ' as ') !== false) {
                    list($file, $var) = explode(' as ', $import, 2);
                    $file_content = self::get_external_content(
                                        dirname($phpt_file), $file);
                    $file_content = trim($file_content);
                    $phpt['import'] .= "\$import['{$var}'] = <<<IMPORT".
                                       "\n{$file_content}\nIMPORT;\n";
                } else {
                    $file_content = self::get_external_content(
                                        dirname($phpt_file), $import);
                    $file_content = trim($file_content);
                    if (substr($file_content, 0, 5) === '<?php') {
                        $file_content = substr($file_content, 5);
                    }
                    if (substr($file_content, -2) === '?>') {
                        $file_content = substr($file_content, 0, -2);
                    }
                    $phpt['import'] .= "{$file_content}\n";
                }
            }
            $phpt['import'] .= "?>";
        } else $phpt['import'] = '';

        return $phpt;
    }

    /**
     * Process expectf
     * @param  string $expect
     * @return string
     */
    private static function process_expectf($expect) {
        // do preg_quote, but miss out any %r delimited sections
        $temp   = "";
        $r      = "%r";
        $length = strlen($expect);
        $start_offset = 0;

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
     * Get header <?php ?> which will include and run core.
     * @return string
     */
    private static function mk_loader() {
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
     * Take collection of keys and get first one which isset
     * @param  array  $array
     * @param  array  $keys
     * @return mixed  string (key) or false (if not found)
     */
    private static function get_first_key(array $array, array $keys) {
        foreach ($keys as $key) {
            if (array_key_exists($key, $array)) {
                return $key;
            }
        }
        return false;
    }
    /**
     * Convert PHPT ini to array
     * @param  string $input
     * @return array
     */
    private static function ini_to_array($input) {
        $output = [];

        foreach($input as $setting) {
            if (strpos($setting, '=') !== false) {
                $setting = explode("=", $setting, 2);
                $name = trim($setting[0]);
                $value = trim($setting[1]);

                if ($name == 'extension') {
                    if (!isset($output[$name])) {
                        $output[$name] = array();
                    }
                    $output[$name][] = $value;
                } else {
                    $output[$name] = $value;
                }
            }
        }

        return $output;
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
     * Get external file contents.
     * @param  string $filename
     * @param  string $file
     * @return string
     */
    private static function get_external_content($path, $file) {
        $filename = fs::ds($path, $file);
        if (!file::exists($filename)) {
            throw new framework\exception\not_found(
                "External file not found: `{$filename}`", 3);
        } else {
            return trim(file::read($filename));
        }
    }
}
