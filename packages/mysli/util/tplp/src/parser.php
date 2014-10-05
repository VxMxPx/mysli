<?php

namespace mysli\util\tplp;

__use(__namespace__,
    './exception/parser',
    'mysli/framework/json',
    'mysli/framework/type/str',
    'mysli/framework/fs/{fs,file}',
    ['mysli/framework/exception/*' => 'framework/exception/%s']
);

class parser {

    private static $functions = [
        "abs"           => "abs(%seg)",
        "ucfirst"       => "ucfirst(%seg)",
        "ucwords"       => "ucwords(%seg)",
        "lower"         => "strtolower(%seg)",
        "upper"         => "strtoupper(%seg)",
        "date"          => "date(%1, strtotime(%seg))",
        "join"          => "implode(%1, %seg)",
        "split"         => "explode(%1, %seg, ...)",
        "length"        => "strlen(%seg)",
        "word_count"    => "str_word_count(%seg)",
        "count"         => "count(%seg)",
        "nl2br"         => "nl2br(%seg)",
        "number_format" => "number_format(%seg, ...)",
        "replace"       => "sprintf(%seg, ...)",
        "round"         => "round(%seg, ...)",
        "floor"         => "floor(%seg)",
        "ceil"          => "ceil(%seg)",
        "strip_tags"    => "strip_tags(%seg, ...)",
        "show_tags"     => "htmlspecialchars(%seg)",
        "trim"          => "trim(%seg, ...)",
        "slice"         => "( is_array(%seg) ? array_slice(%seg, ...) : substr(%seg, ...) )",
        "word_wrap"     => "wordwrap(%seg, %1, '<br/>')",
        "max"           => "max(%seg, ...)",
        "min"           => "min(%seg, ...)",
        "column"        => "array_column(%seg, %1, ...)",
        "reverse"       => "( is_array(%seg) ? array_reverse(%seg) : strrev(%seg) )",
        "contains"      => "( (is_array(%seg) ? in_array(%1, %seg) : strpos(%seg, %1)) !== false )",
        "key_exists"    => "array_key_exists(%1, %seg)",
        "sum"           => "array_sum(%seg)",
        "unique"        => "array_unique(%seg)",
        "range"         => "range(%1, %2, ...)",
        "random"        => "rand(%1, %2)"
    ];

    /**
     * Parse particular file (this will handle includes)
     * @param  string $filename
     * @param  string $root
     * @return string
     */
    static function file($filename, $root) {
        // Prepare input
        $fullpath = fs::ds($root, $filename);
        if (!file::exists($fullpath)) {
            throw new framework\exception\not_found(
                        "Template file not found: `{$fullpath}`", 1);
        }
        $template = file::read($fullpath);

        try {
            return self::process($template);
        } catch (\Exception $e) {
            throw new exception\parser(
                "Parse of file: `{$filename}` failed with message: ".
                $e->getMessage(), 1);
        }
    }
    /**
     * Process template
     * @param  string $template
     * @return string
     */
    static function process($template) {
        $template = str::to_unix_line_endings($template);
        $lines    = explode("\n", $template);
        // Parsed template lines and headers (those are actual php commands)
        $output  = [];
        $headers = [];
        // Buffer (multi-line commands)
        $buffer['start']    = 0;
        $buffer['contents'] = '';
        $buffer['close']    = '';
        $buffer['write']    = false;
        // Open tags to throw accurate exceptions, on where an error happened
        $open_tags = [
            'for' => [0, 0],
            'if'  => [0, 0]
        ];

        foreach ($lines as $lineno => $line) {

            // Escape \{ and \}
            $line = self::escaped_cbrackets($line, true);

            // Check buffer for close *} and }}}
            // If buffer was closed, it will be added to the output
            if (($endbuffer = self::process_end_buffer($line, $buffer))) {
                $output[] = $endbuffer;
            }

            // This is buffer, and it's not closed
            if ($buffer['close']) {
                if ($buffer['write']) {
                    $buffer['contents'] .= "\n{$line}";
                }
                continue;
            }

            // Escape single quotes inside curly brackets {''}
            $line = self::escape_single_quotes($line, true);

            // Find block regions {{{ and {*
            self::process_bregions($line, $lineno, $buffer);

            try {
                // Find variables and functions
                $line = self::process_var_and_func($line);

                // Find ::if and ::elif
                list($line, $opened) = self::process_if($line);
                if ($opened) {
                    $open_tags['if'][0]++;
                    $open_tags['if'][1] = $lineno;

                }
                // Find: ::else ::/if ::/for ::break ::continue
                list($line, $closed) = self::process_special_tags($line);
                if (array_key_exists($closed, $open_tags)) {
                    $open_tags[$closed][0]--;
                }

                // Find: ::for <id>, <var> in <collection>
                list($line, $opened) = self::process_for($line);
                if ($opened) {
                    $open_tags['for'][0]++;
                    $open_tags['for'][1] = $lineno;
                }

                // Translation key: {@TRASNLATE}, {@TR(n)}, {@TR var}
                $line = self::process_translation($line);
            } catch (\Exception $e) {
                throw new exception\parser(self::f_error(
                    $lines, $lineno, $e->getMessage()));
            }

            // Restore escaped curly brackets and single quotes
            $line = self::escaped_cbrackets($line, false);
            $line = self::escape_single_quotes($line, false);

            // Add the buffer
            if ($buffer['contents']) {
                $output[] = rtrim($buffer['contents']);
                $buffer['contents'] = '';
            }

            // Add the line
            if (trim($line)) {
                $output[] = $line;
            }
        }

        if ($open_tags['if'][0] > 0) {
            throw new exception\parser(self::f_error(
                $lines, $open_tags['if'][1], "Unclosed `if` statement."));
        }
        if ($open_tags['for'][0] > 0) {
            throw new exception\parser(self::f_error(
                $lines, $open_tags['for'][1], "Unclosed `for` statement."));
        }
        if ($buffer['close']) {
            throw new exception\parser(self::f_error(
                $lines, $buffer['start'], "Unclosed region."));
        }

        return implode("\n", $output);
    }

    // Private methods

    /**
     * Process translation tags {@KEY}
     * @param  string  $line
     * @return string
     */
    private static function process_translation($line) {
        return preg_replace_callback('/{@([A-Z0-9_]+)(?:\((.*?)\))?(.*?)}/',
        function ($match) {
            $key = trim($match[1]);
            $plural = trim($match[2]);
            if (!is_numeric($plural)) {
                $plural = self::parse_variable_with_functions($plural);
            }
            // Process variables
            $variables = explode(',', trim($match[3]));
            foreach ($variables as &$var) {
                try {
                    $var = self::parse_variable(trim($var));
                } catch (\Exception $e) {
                    if ($e->getCode() !== 1) {
                        throw $e;
                    } else {
                        $var = "''";
                    }
                }
            }
            $variables = implode(', ', $variables);
            $variables = trim($variables, "'") ? ', [' . $variables . ']' : '';
            // Do we have plural?
            $key = $plural ? "['$key', $plural]" : "'{$key}'";
            return '<?php echo $tplp_translator_service(' .
                   $key . $variables . '); ?>';
        }, $line);
    }
    /**
     * Process ::for statement
     * @param  string $line
     * @return array  [string, boolean]
     */
    private static function process_for($line) {
        $opened = false;
        $line = preg_replace_callback(
        '/::for ([a-zA-Z0-9\_]+\,\ ?)?([a-zA-Z0-9\_]+) in (.*)/',
        function ($match) use (&$opened) {
            $key = '';
            try {
                $key = self::parse_variable(trim($match[1], ', '));
            } catch (\Exception $e) {} // Pass
            $val = self::parse_variable($match[2]);
            $var = self::parse_variable_with_functions($match[3]);
            $exp = $key ? "{$key} => {$val}" : $val;
            $opened = true;
            return '<?php foreach (' . $var . ' as ' . $exp . '): ?>';
        }, $line);

        return [$line, $opened];
    }
    /**
     * Process special tags like: ::continue, ::break, ::else, ::/for, ::/if
     * @param  string $line
     * @return array  [string, string]
     */
    private static function process_special_tags($line) {
        $type = '';
        $line = preg_replace_callback('#::(else|break|continue|/if|/for)#',
        function ($match) use (&$type) {
            switch ($match[1]) {
                case 'continue' :
                    $type = 'continue';
                    return '<?php continue; ?>';
                case 'break'    :
                    $type = 'break';
                    return '<?php break; ?>';
                case 'else'     :
                    $type = 'else';
                    return '<?php else: ?>';
                case '/for'     :
                    $type = 'for';
                    return '<?php endforeach; ?>';
                case '/if'      :
                    $type = 'if';
                    return '<?php endif; ?>';
            }
        }, $line);

        return [$line, $type];
    }
    /**
     * Process ::if and ::elif
     * @param  string  $line
     * @return array   [string, boolean]
     */
    private static function process_if($line) {
        $opened = false;
        $line = preg_replace_callback('/::(if|elif) (.*)/',
        function ($match) use (&$opened) {
            $statement = $match[2];

            $statement = preg_replace_callback('/([\ !(]*)(.*?)( != | !== | '.
                '=== | == | &lt; | < | &gt; | > | <= | &lt;= | >= | &gt;= | '.
                '\\|\\| | && | OR | AND |$)/',
            function ($match) {
                // Special characters: (, !
                $mod = $match[1];
                // Variable / function / boolean, numeric, null
                $variable = $match[2];
                // AND, OR, !=, ==, <, >, <=, >=
                $logical = str_replace(['==', '!=', '&lt;', '&gt;'],
                                       ['===', '!==', '<', '>'],
                                       $match[3]);
                $variable = self::parse_variable_with_functions($variable);
                return trim($mod . $variable . ' ' . trim($logical)) . ' ';
            }, $statement);

            $type = ($match[1] === 'elif' ? 'elseif' : 'if');
            // Line and opened status
            $opened = ($type === 'if');
            return '<?php ' . $type.' ('.trim($statement) . '): ?>';
        }, $line);

        return [$line, $opened];
    }
    /**
     * Find variables and functions.
     * Variables: {var}, {var[key]}, {var->prop}
     * Functions: {var|func}, {var|func:var,'param'},
     * {var|ns/method:var,'param'}
     * @param  string  $line
     * @return string
     */
    private static function process_var_and_func($line) {
        $line = preg_replace_callback('/\{(?=[^@])(.*?)\}/',
        function ($match) {
            // no echo {((variable))}
            if (substr($match[1], 0, 2) === '(('
                && substr($match[1], -2) === '))') {
                $match[1] = substr($match[1], 2, -2);
                $echo = '';
            } else {
                $echo = 'echo ';
            }
            $var = self::parse_variable_with_functions(trim($match[1]));
            if (trim($var) === '') {
                throw new exception\parser(
                    "Not a valid variable (empty): ``", 1);
            }
            return '<?php ' . $echo . $var . '; ?>';
        }, $line);

        return $line;
    }
    /**
     * Process block regions like: {{{ and {*
     * @param  string  $line
     * @param  integer $lineno
     * @param  array   $buffer
     * @return null
     */
    private static function process_bregions(&$line, $lineno, array &$buffer) {
        // {{{
        while (strpos($line, '{{{') !== false) {
            list($line, $buffer['contents']) = explode('{{{', $line, 2);
            $buffer['start'] = $lineno;
            $buffer['write'] = true;
            $buffer['close'] = '}}}';
            if (strpos($buffer['contents'], '}}}') !== false) {
                list($bufferoff, $lineoff) = explode('}}}',
                                                     $buffer['contents'], 2);
                $line = $line . $lineoff;
                $buffer['contents'] = $buffer['contents'] . $bufferoff;
                $buffer['close'] = '';
                $buffer['start'] = 0;
            }
        }
        // {*
        while (strpos($line, '{*') !== false) {
            list($line, $commenton) = explode('{*', $line, 2);
            $buffer['start'] = $lineno;
            $buffer['write'] = false;
            $buffer['close'] = '*}';
            if (strpos($commenton, '*}') !== false) {
                list($_, $lineoff) = explode('*}', $commenton, 2);
                $line = $line . $lineoff;
                $buffer['close'] = '';
                $buffer['start'] = 0;
            }
        }
    }
    /**
     * Process end buffer (like *} and }}})
     * @param  string $line
     * @param  array  $buffer
     * @return string
     */
    private static function process_end_buffer(&$line, array &$buffer) {
        $output = false;
        if ($buffer['close'] && strpos($line, $buffer['close']) !== false) {
            list($bufferoff, $line) = explode($buffer['close'], $line, 2);
            if ($buffer['write']) {
                $output = rtrim($buffer['contents'] . $bufferoff);
            }
            $buffer['contents'] = '';
            $buffer['start']    = 0;
            $buffer['close']    = '';
            $buffer['write']    = false;
        }
        return $output;
    }
    /**
     * Process escaped curly brackets, e.g.: \{ and \}
     * @param  string  $line
     * @param  boolean $protect
     * @return string
     */
    private static function escaped_cbrackets($line, $protect) {
        if ($protect) {
            $line = str_replace('\\{', '--MYSLI-CB-OPEN', $line);
            $line = str_replace('\\}', '--MYSLI-CB-CLOSE', $line);
        } else {
            $line = preg_replace('/--MYSLI-CB-OPEN/',  '{',  $line);
            $line = preg_replace('/--MYSLI-CB-CLOSE/', '}',  $line);
        }
        return $line;
    }
    /**
     * Protect things wrapped in {''}
     * @param  string  $line
     * @param  boolean $protect protection on/off
     * @return string
     */
    private static function escape_single_quotes($line, $protect) {
        if ($protect) {
            // Protect everything wrapped in ''
            $line =
            preg_replace_callback("/{(.*?)}/", function ($match) {
                $match = $match[1];
                return '{' . preg_replace_callback("/'(.*?)'/",
                function ($match) {
                    return '--MYSLI-QUOT-ST-' . base64_encode($match[1]) .
                           '-MYSLI-END-QUOT';
                }, $match) . '}';
            }, $line);
        } else {
            // Restore everything wrapped in ''
            $line =
            preg_replace_callback("/--MYSLI-QUOT-ST-(.*?)-MYSLI-END-QUOT/",
            function ($match) {
                return '\'' . base64_decode($match[1]) . '\'';
            }, $line);
        }

        return $line;
    }
    /**
     * Process variable + function string. var|func|func
     * @param  string $line
     * @return string
     */
    private static function parse_variable_with_functions($line) {
        $line = trim($line);
        // coud be 0
        if (!$line) {
            return $line;
        }
        // Check if there's variable
        if (substr($line, 0, 1) === '|') {
            $has_var = false;
            $line = substr($line, 1);
        } else $has_var = true;

        $segments = explode('|', $line);
        if ($has_var) {
            $variable = array_shift($segments);
            $variable = self::parse_variable($variable);
        }

        $processed = '%seg';
        $segments = array_reverse($segments);
        foreach ($segments as $segment) {
            $processed = str_replace('%seg',
                                     self::parse_functions($segment),
                                     $processed);
        }

        if (!$has_var) {
            if (preg_match('/\\$?[a-z0-9\\:_]+\\(%seg/i', $processed, $m)) {
                $m = explode("(", $m[0], 2)[0];
                // var_dump(substr($m, 0, 1) !== '$' && !strpos($m, '::'));
                if (substr($m, 0, 1) !== '$' && !strpos($m, '::')) {
                    throw new exception\parser(
                        "Function require argument in format `arg|func`: ".
                        "`{$m}`", 2);
                }
            }
            return str_replace(['%seg, ', '%seg'], '', $processed);
        } else return str_replace('%seg', $variable, $processed);
    }
    /**
     * Convert variables to PHP format.
     * Example: something[key]->property to $something['key']->property
     * Must be valid variable format! Return unchanged:
     * numbers, true, false, null...
     * @param  string $variable
     * @return string
     */
    private static function parse_variable($variable) {
        $variable = trim($variable);
        // Check if we have valid variable
        if ($variable === '') {
            throw new exception\parser(
                "Not a valid variable (empty): ``", 1);
        }
        if (substr($variable, 0, 8) === '--MYSLI-') {
            return $variable;
        }
        if (is_numeric($variable)) return $variable;
        if (in_array($variable, ['true', 'false', 'null', "''"])) {
            return $variable;
        }
        // Encoded string!
        if (substr($variable, 0, 1) === '-' && is_numeric($variable)) {
            return $variable;
        }

        $variable = preg_replace_callback('/\[(.*?)\]/', function ($match) {
            if (substr($match[1], 0, 1) === '\\') {
                return '[' . substr($match[1], 1) . ']';
            } else return "['{$match[1]}']";
        }, $variable);

        if (substr($variable, 0, 1) === '$') {
            throw new exception\parser(
                "Variable name cannot start with `$`: `{$variable}`", 2);
        }
        if (!preg_match(
            '/^[a-z0-9_]+((\\-\\>[a-z0-9_]+)|(\\[\'.+\'\\]))?$/i', $variable)) {
            throw new exception\parser(
                "Not a valid variable name: `{$variable}`", 4);
        }
        if (is_numeric(substr($variable, 0, 1))) {
            throw new exception\parser(
                "Variable name cannot start with number: `{$variable}`", 3);
        }

        return '$' . $variable;
    }
    /**
     * Convert functions to PHP format.
     * @param  string $function
     * @return string
     */
    private static function parse_functions($function) {
        // Put function to meaningful pieces
        $function = trim($function);
        $segments = explode(':', $function);
        $function = trim(array_shift($segments));

        // Check if we any have segments...
        if (!isset($segments[0])) $segments = [];
        else $segments = explode(',', $segments[0]);

        // Process segments (parameters)
        foreach ($segments as $key => $segment) {
            try {
                $segments[$key] = self::parse_variable($segment);
            } catch (\Exception $e) {
                if ($e->getCode() !== 1) { throw $e; }
            }
        }

        // If it's one of the native function, then we'll set it as such...
        if (isset(self::$functions[$function])) {
            $function = self::$functions[$function];
            if (strpos($function, '%1') !== false) {
                foreach ($segments as $key => $segment) {
                    if (strpos($function, '%' . ($key + 1))) {
                        $function = str_replace('%' . ($key + 1),
                                                $segment, $function);
                        unset($segments[$key]);
                    }
                }
                if (preg_match('/%[0-9]+/', $function)) {
                    throw new exception\parser(
                        "Missing parameter: `{$function}`", 1);
                }
            }
            return str_replace(
                    ', ...',
                    ($segments ? ', ' . implode(', ', $segments) : ''),
                    $function);
        }

        if (is_numeric(substr($function, 0, 1))) {
            throw new exception\parser(
                "Function name cannot start with number: `{$function}`", 2);
        }
        if (!preg_match('/^[a-z0-9_\\/]+$/i', $function)) {
            throw new exception\parser(
                "Not a valid function name: `{$function}`", 3);
        }

        // Imported static method call: blog/method => blog::method
        if (strpos($function, '/') !== false) {
            $sfunction = explode('/', $function);
            if (count($sfunction) !== 2) {
                throw new exception\parser(
                    "Function `{$function}` is not valid too many segments", 3);
            }
            $function = implode('::', $sfunction);
        } else {
            // Variable function
            $function = '$tplp_func_' . $function;
        }

        // NOT a native function, nor method set it to be variable (func)...
        if (empty($segments)) {
            return $function . '(%seg)';
        } else {
            return $function . '(%seg, ' . implode(', ', $segments) . ')';
        }
    }
    /**
     * Format generic exception message.
     * @param  integer $lines
     * @param  integer $current
     * @param  string  $message
     * @return string
     */
    private static function f_error($lines, $current, $message) {
        return $message . "\n" . self::err_lines($lines, $current, 3);
    }
    /**
     * Return -$padding, $current, +$padding lines for exceptions, e.g.:
     *   11. ::if true
     * >>12.     {username|non_existant_function}
     *   13. ::/if
     * @param  array   $lines
     * @param  integer $current
     * @param  integer $padding
     * @return string
     */
    private static function err_lines($lines, $current, $padding=3) {
        $start    = $current - $padding;
        $end      = $current + $padding;
        $result   = '';
        for ($position = $start; $position < $end; $position++) {
            if (isset($lines[$position])) {
                if ($position === $current) {
                    $result .= ">>";
                } else {
                    $result .= "  ";
                }
                $result .= ($position+1).". {$lines[$position]}\n";
            }
        }
        return $result;
    }
}
