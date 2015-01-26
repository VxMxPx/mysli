<?php

/**
 * Determine if this is command line interface.
 * @return boolean
 */
function is_cli() {
    return php_sapi_name() === 'cli' || defined('STDIN');
}

/**
 * Output variable as: <pre>print_r($variable)</pre> (this is only for debuging)
 * This will die after dumpign variables on screen.
 */
function dump() {
    die(call_user_func_array('dump_rr', func_get_args()));
}

/**
 * Dump, but don't die - echo results instead.
 * @return null
 */
function dump_r() {
    echo call_user_func_array('dump_rr', func_get_args());
}

/**
 * Dump, but don't die - return results instead.
 * @return string
 */
function dump_rr() {
    $arguments = func_get_args();
    $result = '';

    foreach ($arguments as $variable)
    {
        if (is_bool($variable)) {
            $bool = $variable ? 'true' : 'false';
        }
        else {
            $bool = false;
        }

        $result .= (!is_cli()) ? "\n<pre>\n" : "\n";
        $result .= '' . gettype($variable);
        $result .= (is_string($variable) ? '['.strlen($variable).']' : '');
        $result .=  ': ' . (is_bool($variable) ? $bool : print_r($variable, true));
        $result .= (!is_cli()) ? "\n</pre>\n" : "\n";
    }

    return $result;
}

/**
 * Used to unline long lines, e.g. - it will remove \n symbold and trim lines.
 * This can be used to comfortably disply long lines of text.
 * @param  string $string
 * @return string
 */
function l($string) {
    return preg_replace('/(\n[ \t]*)/ms', ' ', $string);
}

/**
 * Inject shortcut.
 * @param  string $namespace
 * @param  string $use
 * @return null
 */
function __use($namespace, $use) {

    $namespace = str_replace('\\', '/', $namespace);
    $lines = explode("\n", $use);

    $segments = explode('/', $namespace);

    if (file_exists(
        MYSLI_PKGPATH . '/' .
        implode('/', array_slice($segments, 0, 2)) . '/' .
        'mysli.pkg.ym'))
    {
        $package = implode('/', array_slice($segments, 0, 2));
    } else {
        $package = implode('/', array_slice($segments, 0, 3));
    }

    foreach ($lines as $line) {

        $line = trim(strtolower($line));

        // Empty line, skip
        if (empty($line)) {
            continue;
        }

        // Comment, skip
        if (substr($line, 0, 1) === '#') {
            continue;
        }

        // is it internal?
        if (substr($line, 0, 1) === '.') {
            $line = $package . substr($line, 1);
        }

        // Contains AS?
        if (strpos($line, ' as ')) {
            list($from, $as) = explode(' as ', $line, 2);
            $as   = trim($as);
            $from = trim($from);
        } else {
            $from = $line;
            $as   = substr($line, strrpos($line, '/')+1);
        }

        // is multiple insersion?
        if (strpos($from, ',')) {

            $segments_from = explode('/', $from);
            $from = implode('/', array_slice($segments_from, 0, -1));
            $last_from = trim(array_slice($segments_from, -1)[0], '{}');
            $multiple_from = explode(',', $last_from);

            if (strpos($as, ',')) {
                $segments_as = explode('/', $as);
                $as = implode('/', array_slice($segments_as, 0, -1));
                $last_as = trim(array_slice($segments_as, -1)[0], '{}');
                $multiple_as = explode(',', $last_as);
                if (count($multiple_as) !== count($multiple_from)) {
                    throw new \Exception(
                        "Expected the same amout of elements: `{$line}` ".
                        "when using `AS`. (".
                        implode(',', $multiple_from).") != (".
                        implode(',', $multiple_as).")");
                }
            } else {
                $multiple_as = $multiple_from;
            }

            foreach ($multiple_from as $k => $file) {
                if (strpos($as, '{...}')) {
                    $asf = str_replace('{...}', $multiple_as[$k], $as);
                } else {
                    $asf = trim($as.'/'.$multiple_as[$k], '/');
                }
                $asf = $namespace . '/' . $asf;
                \core\autoloader::add_alias("{$from}/{$file}", $asf);
            }

            continue;
        }

        $as = $namespace . '/' . $as;
        \core\autoloader::add_alias($from, $as);
    }

    return true;
}
