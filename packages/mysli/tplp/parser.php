<?php

namespace Mysli\Tplp;

class Parser
{
    private $template;
    private static $native_functions = [
        'abs'           => 'abs(%seg)',
        'ucfirst'       => 'ucfirst(%seg)',
        'ucwords'       => 'ucwords(%seg)',
        'lower'         => 'strtolower(%seg)',
        'upper'         => 'strtoupper(%seg)',
        'date'          => 'date(%1, strtotime(%seg))',
        'join'          => 'implode(%1, %seg)',
        'split'         => 'explode(%1, %seg, ...)',
        'length'        => 'strlen(%seg)',
        'word_count'    => 'str_word_count(%seg)',
        'count'         => 'count(%seg)',
        'nl2br'         => 'nl2br(%seg)',
        'number_format' => 'number_format(%seg, ...)',
        'replace'       => 'sprintf(%seg, ...)',
        'round'         => 'round(%seg, ...)',
        'floor'         => 'floor(%seg)',
        'ceil'          => 'ceil(%seg)',
        'strip_tags'    => 'strip_tags(%seg, ...)',
        'show_tags'     => 'htmlspecialchars(%seg)',
        'trim'          => 'trim(%seg, ...)',
        'slice'         => '( is_array(%seg) ? array_slice(%seg, ...) : substr(%seg, ...) )',
        'word_wrap'     => 'wordwrap(%seg, %1, \'<br/>\')',
        'max'           => 'max(%seg, ...)',
        'min'           => 'min(%seg, ...)',
        'column'        => 'array_column(%seg, %1, ...)',
        'reverse'       => '( is_array(%seg) ? array_reverse(%seg) : strrev(%seg) )',
        'contains'      => '( (is_array(%seg) ? in_array(%1, %seg) : strpos(%seg, %1)) !== false )',
        'key_exists'    => 'array_key_exists(%1, %seg)',
        'sum'           => 'array_sum(%seg)',
        'unique'        => 'array_unique(%seg)'
    ];

    /**
     * Construct Parser.
     * --
     * @param string $template
     */
    public function __construct($template)
    {
        $this->template = $template;
    }

    /**
     * Convert functions to PHP format.
     * --
     * @param  string $function
     * --
     * @throws \Mysli\Tplp\ParserException If function parameters are missing.
     * --
     * @return string
     */
    private function parse_functions($function)
    {
        // Put function to meaningful pieces
        $function = trim($function);
        $segments = explode(':', $function);
        $function = trim(array_shift($segments));

        // Check if we any have segments...
        if (!isset($segments[0])) $segments = [];
        else $segments = explode(',', $segments[0]);

        // Process segments (parameters)
        foreach ($segments as $key => $segment) {
            $segments[$key] = $this->parse_variable($segment);
        }

        // If it's one of the native function, then we'll set it as such...
        if (isset(self::$native_functions[$function])) {
            $function = self::$native_functions[$function];
            if (strpos($function, '%1') !== false) {
                foreach ($segments as $key => $segment) {
                    if (strpos($function, '%' . ($key + 1))) {
                        $function = str_replace('%' . ($key + 1), $segment, $function);
                        unset($segments[$key]);
                    }
                }
                if (preg_match('/%[0-9]+/', $function)) {
                    throw new \Mysli\Tplp\ParserException(
                        "Missing parameters: `{$function}`", 1
                    );
                }
            }
            return str_replace(
                ', ...',
                ( $segments ? ', ' . implode(', ', $segments) : '' ),
                $function
            );
        }

        // NOT a native function, set it to be variable (func)...
        if (empty($segments)) {
            return '$tplp_func_' . $function . '(%seg)';
        } else {
            return '$tplp_func_' . $function . '(%seg, ' . implode(', ', $segments) . ')';
        }
    }

    /**
     * Convert variables to PHP format.
     * Example: something[key]->property to $something['key']->property
     * Must be valid variable format! Return unchanged:
     * numbers, true, false, null...
     * --
     * @param  string $variable
     * --
     * @return string
     */
    private function parse_variable($variable)
    {
        $variable = trim($variable);

        // Check if we have valid variable
        if (!$variable) return $variable; // can be 0
        if (is_numeric($variable)) return $variable;
        if (in_array($variable, ['true', 'false', 'null'])) return $variable;
        // Encoded string!
        if (substr($variable, 0, 1) === '-') return $variable;

        $variable = preg_replace_callback('/\[(.*?)\]/', function ($match) {
            if (substr($match[1], 0, 1) === '\\') {
                return '[' . substr($match[1], 1) . ']';
            } else return "['{$match[1]}']";
        }, $variable);
        return '$' . $variable;
    }

    /**
     * Process variable + function string.
     * --
     * @param  string $input
     * --
     * @return string
     */
    private function parse_variable_with_functions($input)
    {
        $input = trim($input);
        if (!$input) return $input;

        $segments = explode('|', $input);
        $variable = array_shift($segments);
        $variable = $this->parse_variable($variable);

        $processed = '%seg';
        $segments = array_reverse($segments);
        foreach ($segments as $segment) {
            $processed = str_replace('%seg', $this->parse_functions($segment), $processed);
        }

        return str_replace('%seg', $variable, $processed);
    }

    /**
     * Parse the template, and return it.
     * --
     * @return string
     */
    public function parse()
    {
        $template = $this->template;

        /* ---------------------------------------------------------------------
         * Encode escaped characters: \{ \} \'
         */
        $template = preg_replace('/\\\{/', '--MYSLI-CB-OPEN',  $template);
        $template = preg_replace('/\\\}/', '--MYSLI-CB-CLOSE', $template);
        $template = preg_replace("/\\\'/", '--MYSLI-APOST',    $template);

        /* ---------------------------------------------------------------------
         * Encode protected regions: {{{ }}}
         */
        $template =
        preg_replace_callback('/\{\{\{(.*?)\}\}\}/ms', function ($match) {
            $match = trim($match[1]);
            if ($match) {
                return '--MYSLI-RAW-' . base64_encode($match) . '-MYSLI-END-RAW';
            }
        }, $template);

        /* ---------------------------------------------------------------------
         * Remove comments: {* *}
         */
        $template = preg_replace('/\{\*.*?\*\}/ms', '', $template);

        /* ---------------------------------------------------------------------
         * Protect everything wrapped in ''
         */
        $template =
        preg_replace_callback("/'(.*?)'/ms", function ($match) {
            if ($match[1]) {
                return '--MYSLI-QUOT-ST-' . base64_encode($match[1]) . '-MYSLI-END-QUOT';
            }
        }, $template);

        /* ---------------------------------------------------------------------
         * Variables: {var}, {var[key]}, {var->prop}
         * Functions: {var|func}, {var|func:var,'param'}
         */
        $template =
        //preg_replace_callback('/\{(?=[a-zA-Z_\ ])(.*?)\}/', function ($match) {
        //                          ^ limit variables to actually be valid,
        //                            unable to call functions on their own, like
        //                            {'m d Y'|date} => date('m d Y')
        preg_replace_callback('/\{(?=[^@])(.*?)\}/', function ($match) {
            return '<?php echo ' . $this->parse_variable_with_functions($match[1]) . '; ?>';
        }, $template);

        /* ---------------------------------------------------------------------
         * If, elif: ::if <statement>, ::elif <statement>
         */
        $template =
        preg_replace_callback('/::(if|elif) (.*)/', function ($match) {
            $statement = $match[2];
            $statement =
            preg_replace_callback(
                '/([\ !(]*)(.*?)( != | == | < | > | <= | >= | OR | AND |$)/',
                function ($match) {
                    // Special characters: (, !
                    $mod      = $match[1];
                    // Variable / function / boolean, numeric, null
                    $variable = $match[2];
                    // AND, OR, !=, ==, <, >, <=, >=
                    $logical  = str_replace(['==', '!='], ['===', '!=='], $match[3]);

                    $variable = $this->parse_variable_with_functions($variable);
                    return trim($mod . $variable . ' ' . trim($logical)) . ' ';
                }, $statement);
            return '<?php ' . ($match[1] === 'elif' ? 'elseif' : 'if') . ' (' . trim($statement) . '): ?>';
        }, $template);

        /* ---------------------------------------------------------------------
         * Special: ::else, ::/if, ::/for, ::break, ::continue
         */
        $template =
        preg_replace_callback('#::(else|break|continue|/if|/for)#', function ($match) {
            switch ($match[1]) {
                case 'continue' : return '<?php continue; ?>';
                case 'break'    : return '<?php break; ?>';
                case 'else'     : return '<?php else: ?>';
                case '/for'     : return '<?php endforeach; ?>';
                case '/if'      : return '<?php endif; ?>';
            }
        }, $template);

        /* ---------------------------------------------------------------------
         * For: ::for <id>, <var> in <collection>
         */
        $template =
        preg_replace_callback('/::for ([a-zA-Z0-9\_]+\,\ ?)?([a-zA-Z0-9\_]+) in (.*)/', function ($match) {
            $key = $this->parse_variable(trim($match[1], ', '));
            $val = $this->parse_variable($match[2]);
            $var = $this->parse_variable_with_functions($match[3]);
            $exp = $key ? "{$key} => {$val}" : $val;
            return '<?php foreach (' . $var . ' as ' . $exp . '): ?>';
        }, $template);

        /* ---------------------------------------------------------------------
         * Translation key: {@TRASNLATE}, {@TR(n)}, {@TR var}
         */
        $template =
        preg_replace_callback('/{@([A-Z0-9_]+)(?:\((.*?)\))?(.*?)}/ms', function ($match) {
            $key = trim($match[1]);
            $plural = trim($match[2]);
            $plural = is_numeric($plural) ? $plural : $this->parse_variable_with_functions($plural);
            // Process variables
            $variables = trim($match[3]);
            $variables = explode(',', $variables);
            foreach ($variables as &$var) {
                $var = $this->parse_variable(trim($var));
            }
            $variables = implode(', ', $variables);
            $variables = $variables ? ', [' . $variables . ']' : '';
            // Do we have plural?
            $key = $plural ? "['$key', $plural]" : "'{$key}'";
            return '<?php echo $tplp_translator_service('.$key.$variables.'); ?>';
        }, $template);

        /* ---------------------------------------------------------------------
         * Restore escaped characters
         */
        $template = preg_replace('/--MYSLI-CB-OPEN/',  '{',  $template);
        $template = preg_replace('/--MYSLI-CB-CLOSE/', '}',  $template);
        $template = preg_replace('/--MYSLI-APOST/',    '\'', $template);

        /* ---------------------------------------------------------------------
         * Restore raw regions
         */
        $template =
        preg_replace_callback('/--MYSLI-RAW-(.*?)-MYSLI-END-RAW/ms', function ($match) {
            $match = trim($match[1]);
            if ($match) {
                return base64_decode($match);
            }
        }, $template);

        /* ---------------------------------------------------------------------
         * Restore everything wrapped in ''
         */
        $template =
        preg_replace_callback("/--MYSLI-QUOT-ST-(.*?)-MYSLI-END-QUOT/ms", function ($match) {
            if ($match[1]) {
                return '\'' . base64_decode($match[1]) . '\'';
            }
        }, $template);

        /* ---------------------------------------------------------------------
         * Restore escaped characters within strings
         */
        $template = preg_replace('/--MYSLI-APOST/', '\\\'', $template);

        return $template;
    }
}
