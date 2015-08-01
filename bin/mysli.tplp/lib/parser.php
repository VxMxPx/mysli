<?php

namespace mysli\tplp; class parser
{
    const __use = '
        mysli.toolkit.{
            fs.fs    -> fs,
            type.str -> str,
            fs.file  -> file
        }
        .{ exception.parser }
    ';

    /**
     * Parse particular file (this will generate proper output with
     * namespaces, etc..)
     * --
     * @param string $file
     * @param string $root
     * @param array  $replace
     *        List of `virtual` files, those might be loaded from different
     *        location or not at all. This is meant for overrides of templates.
     *        Array needs to be in following format:
     *        [
     *            // This will replace `file-name` file, with file from:
     *            // $root.$replace_file_name
     *            'file-name' => [ string $root, string $replace_file_name ],
     *            // Or...
     *            // Simply use string, this will not load file from file-system
     *            // but rather just provided string.
     *            'file-name' => 'Template',
     *        ]
     *
     * --
     * @throws mysli\tplp\exception\parser 10 Template file not found.
     * --
     * @return string
     */
    static function file($file, $root, array $replace=[])
    {
        $fullpath = fs::ds($root, $file);
        $filenoext = strtolower(substr($file, 0, -9)); // .tpl.html

        if (!file::exists($fullpath) && !isset($replace[$filenoext]))
        {
            throw new exception\parser(
                "Template file not found: `{$fullpath}`", 10
            );
        }

        // Parse file
        $uses = [];
        $parsed = static::parse($fullpath, $uses, [], null, $replace);

        // Generate namespace
        $namespace = str::clean($filenoext, '<[^a-z0-9\/]+>');
        $namespace = str_replace('/', '\\', $namespace);
        $namespace = "tplp\\template\\{$namespace}";

        // Process uses
        $use = '';

        foreach ($uses as $usear)
        {
            $use .= $usear['statement'] . "\n";
        }

        // Final file format
        return "<?php\nnamespace {$namespace};\n{$use}?>{$parsed}";
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Process template.
     * --
     * @param string $filename
     * @param string $uses
     * @param array  $sets
     * @param string $module  Weather to select only particular module in file.
     * @param array  $replace (@see static::file())
     * --
     * @throws mysli\tplp\exception\parser
     *         10 Module not found.
     *
     * @throws mysli\tplp\exception\parser
     *         20 Closed `::/let` tag before it was opened.
     *
     * @throws mysli\tplp\exception\parser
     *         30 `::let` cannot nested in `::extend`, `::import` or `::set`.
     *
     * @throws mysli\tplp\exception\parser
     *         40 `::let` is already opened on line...
     *
     * @throws mysli\tplp\exception\parser
     *         50 `::set` must be wrapped with `::extend` or `::import`.
     *
     * @throws mysli\tplp\exception\parser
     *         60 Closed `::/set` tag before it was opened.
     *
     * @throws mysli\tplp\exception\parser
     *         70 Trying to close unopened tag.
     *
     * @throws mysli\tplp\exception\parser
     *         80 Unclosed `::set` statement.
     *
     * @throws mysli\tplp\exception\parser
     *         81 Unclosed statement.
     *
     * @throws mysli\tplp\exception\parser
     *         82 Unclosed `::if` statement.
     *
     * @throws mysli\tplp\exception\parser
     *         83 Unclosed `::for` statement.
     *
     * @throws mysli\tplp\exception\parser
     *         84 Unclosed region.
     * --
     * @return string
     */
    protected static function parse(
        $filename, array &$uses=[], array $sets=[], $module=null, array $replace=[])
    {
        // Overrides?
        $filenoext = substr(basename($filename), 0, -9);
        if (isset($replace[$filenoext]) && !is_array($replace[$filenoext]))
        {
            $template = $replace[$filenoext];
        }
        else
        {
            if (isset($replace[$filenoext]) && is_array($replace[$filenoext]))
                $filename = implode('/', $replace[$filenoext]);

            $template = file::read($filename);
        }

        $template = str::to_unix_line_endings($template);

        // Dir name and
        // sfpath, used only in error messages...
        $dirname = dirname($filename);
        $sfpath = $filename;

        if ($module)
        {
            if (preg_match(
                '/^::module '.preg_quote($module, '/').'$\s(.*?)\s^::\/module$/ms',
                $template, $match))
            {
                $template = $match[1];
            }
            else
            {
                throw new exception\parser(
                    "Module `{$module}` not found in `{$sfpath}`", 10
                );
            }
        }

        $lines = explode("\n", $template);

        // Parsed template lines (output),
        // uses (::use)
        // extends (::extend)
        // imports (::import)
        // let (::let)
        $output  = [];
        $extends = [];
        $imports = [];
        $let     = false;

        $in_curr_line = 0;
        $in_curr = false;
        $in_set_line = 0;
        $in_set = false;

        // Block (multi-line commands)
        $block['start']    = 0;
        $block['contents'] = '';
        $block['close']    = '';
        $block['write']    = false;

        // Open tags to throw accurate exceptions, on where an error happened
        $open_tags = [
            'for' => [0, 0],
            'if'  => [0, 0]
        ];

        // Scan lines
        foreach ($lines as $lineno => $line)
        {
            // Find ::/let closed
            if (static::find_close_let($line))
            {
                if (!$let)
                {
                    throw new exception\parser(
                        "Closed `::/let` tag before it was opened.", 20
                    );
                }

                $p = static::process_let($let['lines'], $let['set']);
                $output[] = "<?php {$let['id']} = {$p}; ?>";
                $let = false;
                unset($p);
                continue;
            }

            // We're in ::let
            if ($let)
            {
                $let['lines'][] = $line;
                continue;
            }

            // Escape \{ and \}
            $line = static::escape_curly_brackets($line, true);

            // Check block region for close *} and }}}
            // If block was closed, it will be added to the output
            if (($endblock = static::find_end_block($line, $block)))
            {
                $output[] = $endblock;
            }

            // This is block region, and it's not closed
            if ($block['close'])
            {
                if ($block['write'])
                {
                    $block['contents'] .= "\n{$line}";
                }
                continue;
            }

            // Escape single quotes inside curly brackets {''}
            $line = static::escape_single_quotes($line, true);

            // Find block regions {{{ and {*
            static::find_block_regions($line, $lineno, $block);

            try
            {
                // Find ::print regions
                list($line, $break) = static::find_print($line, $sets);
                if ($break)
                {
                    $output[] = $line;
                    continue;
                }

                // Find ::let (opened)
                if (($let_o = static::find_let($line)))
                {
                    if ($in_curr || $in_set)
                    {
                        throw new exception\parser(
                            "`::let` cannot nested in `::extend`, ".
                            "`::import` or `::set`", 30
                        );
                    }

                    if ($let)
                    {
                        throw new exception\parser(
                            "`::let` is already opened on line: ".
                            "`{$let['lineno']}`", 40
                        );
                    }
                    else
                    {
                        $let = $let_o;
                    }

                    if ($let['closed'])
                    {
                        $p = static::find_var_and_func($let['lines']);
                        $p = static::escape_single_quotes($p, false);
                        $p = static::escape_curly_brackets($p, false);
                        $output[] = "<?php {$let['id']} = {$p}; ?>";
                        $let = false;
                        unset($p);
                    }
                    else
                    {
                        $let['lineno'] = $lineno;
                    }

                    continue;
                }

                // Find ::set opened
                if (($id = static::find_open_set($line)))
                {
                    if ($in_curr === false)
                    {
                        throw new exception\parser(
                            "`::set` must be wrapped with `::extend` or `::import`",
                            50
                        );
                    }

                    $in_set_line = $lineno;
                    $in_curr['set'][$id] = [];
                    $in_set = &$in_curr['set'][$id];
                    continue;
                }

                // Find ::set closed
                if (static::find_close_set($line))
                {
                    if ($in_set === false)
                    {
                        throw new exception\parser(
                            "Closed `::/set` tag before it was opened.", 60
                        );
                    }

                    $in_set = implode("\n", $in_set);
                    unset($in_set);
                    $in_set = false;
                    continue;
                }

                // Find closed ::extend or ::import
                if (($type = static::find_extend_import_close($line)))
                {
                    if ($in_curr === false)
                    {
                        throw new exception\parser(
                            "Trying to close unopened tag.", 70
                        );
                    }

                    if ($type === 'import')
                    {
                        $output[] = static::handle_import(
                            $in_curr['file'],
                            $uses,
                            $in_curr['set'],
                            $in_curr['module'],
                            $dirname,
                            $replace
                        );
                    }

                    unset($in_curr);
                    $in_curr = false;
                    continue;
                }

                // Find ::use statements
                if (($id = static::find_use($line, $uses)))
                {
                    $uses[$id]['debug'][] = [
                        'lines' => err_lines($lines, $lineno),
                        'file'  => $sfpath
                    ];
                    continue;
                }

                // Find ::extend statements
                if (($id = static::find_extend($line, $extends)))
                {
                    if ($extends[$id]['open'])
                    {
                        $in_curr_line = $lineno;
                        $in_curr = &$extends[$id];
                    }

                    $extends[$id]['line'] = $lineno;
                    continue;
                }

                // Find ::import statements
                if (($id = static::find_import($line, $imports)))
                {
                    if ($imports[$id]['open'])
                    {
                        $in_curr_line = $lineno;
                        $in_curr = &$imports[$id];
                    }
                    else
                    {
                        $output[] = static::handle_import(
                            $imports[$id]['file'],
                            $uses,
                            $imports[$id]['set'],
                            $imports[$id]['module'],
                            $dirname,
                            $replace
                        );
                    }

                    continue;
                }

                // Find inline if {var if var else 'No-var'}
                $line = static::find_inline_if($line);

                // Find variables and functions
                $line = static::find_var_and_func($line);

                // Find ::if and ::elif
                list($line, $opened) = static::find_if($line);
                if ($opened)
                {
                    $open_tags['if'][0]++;
                    $open_tags['if'][1] = $lineno;

                }
                // Find: ::else ::/if ::/for ::break ::continue
                list($line, $closed) = static::find_special_tags($line);
                if (array_key_exists($closed, $open_tags))
                {
                    $open_tags[$closed][0]--;
                }

                // Find: ::for <id>, <var> in <collection>
                list($line, $opened) = static::find_for($line);
                if ($opened)
                {
                    $open_tags['for'][0]++;
                    $open_tags['for'][1] = $lineno;
                }

                // Translation key: {@TRASNLATE}, {@TR(n)}, {@TR var}
                $line = static::find_translation($line);
            }
            catch (\Exception $e)
            {
                throw new exception\parser(
                    f_error($lines, $lineno, $e->getMessage(), $sfpath),
                    $e->getCode()
                );
            }

            // Restore escaped curly brackets and single quotes
            $line = static::escape_curly_brackets($line, false);
            $line = static::escape_single_quotes($line, false);

            // Add the block
            if ($block['contents'])
            {
                $output[] = rtrim($block['contents']);
                $block['contents'] = '';
            }

            // Add the line
            if (trim($line))
            {
                if ($in_set !== false)
                {
                    $in_set[] = $line;
                }
                else
                {
                    $output[] = $line;
                }
            }
        }

        if ($in_set !== false)
        {
            throw new exception\parser(
                f_error($lines, $in_set_line, "Unclosed `::set` statement.", $sfpath),
                80
            );
        }

        if ($in_curr !== false)
        {
            throw new exception\parser(
                f_error($lines, $in_curr_line, "Unclosed statement.", $sfpath),
                81
            );
        }

        if ($open_tags['if'][0] > 0)
        {
            throw new exception\parser(
                f_error($lines, $open_tags['if'][1], "Unclosed `::if` statement.", $sfpath),
                82
            );
        }

        if ($open_tags['for'][0] > 0)
        {
            throw new exception\parser(
                f_error($lines, $open_tags['for'][1], "Unclosed `::for` statement.", $sfpath),
                83
            );
        }

        if ($block['close'])
        {
            throw new exception\parser(
                f_error($lines, $block['start'], "Unclosed region.", $sfpath),
                84
            );
        }

        // Implode output
        $output = implode("\n", $output);

        // Process extends
        if (!empty($extends))
        {
            foreach ($extends as $extfile => $extend)
            {
                // Set own output as set
                $extend['set'][$extend['self']] = $output;

                // Try to include and parse file
                try
                {
                    $output = static::handle_import(
                        // $file, $uses, $set,        $module, $dir,     $replace
                        $extfile, $uses, $extend['set'], null, $dirname, $replace
                    );
                    // $path = static::resolve_relative_path($extfile, $dirname);
                    // $output = static::parse(
                    //     $path, $uses, $extend['set'], null, $replace
                    // );
                }
                catch (\Exception $e)
                {
                    throw new exception\parser(
                        f_error($lines, $extend['line'], $e->getMessage()),
                        $e->getCode()
                    );
                }
            }
        }

        return $output;
    }

    /**
     * Find `::let`.
     * --
     * @param string $line
     * --
     * @throws mysli\tplp\exception\parser
     *         10 Missing assign statement (not enough elements).
     *
     * @throws mysli\tplp\exception\parser
     *         20 Not a valid variable name.
     *
     * @throws mysli\tplp\exception\parser
     *         30 Missing assign statement (::let var = 'value').
     * --
     * @return array
     *         [ string id, boolean closed, string lines ] if found,
     *         false otherwise
     */
    protected static function find_let($line)
    {
        if (preg_match(
            '/^[ \t]*?::let ([a-z0-9_]+)( set (.*?))?( \= (.*?))?( do)?$/',
            $line, $match))
        {
            $result = ['lines' => [], 'set' => false];
            $result['id'] = static::parse_variable(trim($match[1]));

            if (count($match) < 3)
            {
                throw new exception\parser(
                    "Missing assign statement (not enough elements).", 10
                );
            }

            if (substr($result['id'], 0, 1) !== '$')
            {
                throw new  exception\parser(
                    "Not a valid variable name: `{$result['id']}`.", 20
                );
            }

            // Do we have do ?
            if (trim($match[count($match)-1]) !== 'do')
            {
                if (substr(trim($match[4]), 0, 1) !== '=')
                {
                    throw new exception\parser(
                        "Missing assign statement (::let var = 'value').", 30
                    );
                }
                $result['lines']  = trim($match[5]);
                $result['closed'] = true;

                return $result;
            }
            else
            {
                array_pop($match); // Remove last element
                $result['closed'] = false;
            }

            if (!empty(trim($match[2])) &&
                !empty(trim($match[3])) &&
                substr(trim($match[2]), 0, 4) === 'set ')
            {
                $result['set'] = trim($match[3]);
            }

            return $result;
        }
        else
        {
            return false;
        }
    }

    /**
     * Process `::let` value.
     * --
     * @param array  $lines
     * @param string $set
     * --
     * @throws mysli\tplp\exception\parser
     *         10 Invalid `set` parameter, expected: ...
     *
     * @throws mysli\tplp\exception\parser
     *         20 Expected parameter in format...
     *
     * @throws mysli\tplp\exception\parser
     *         30 Expected dictionary divider...
     * --
     * @return string
     */
    protected static function process_let(array $lines, $set)
    {
        if (strpos($set, '('))
        {
            $arg = explode('(', $set, 2);
            $set = $arg[0];
            $arg = substr($arg[1], 0, -1);
        }
        else
        {
            $arg = null;
        }

        if ($set && !in_array($set, ['dictionary', 'implode', 'array']))
        {
            throw new exception\parser(
                "Invalid `set` parameter, expected: ".
                "`dictionary`, `implode` or `array`.", 10
            );
        }

        if (in_array($set, ['dictionary', 'implode']) && !$arg)
        {
            throw new exception\parser(
                "Expected parameter for `{$set}` ".
                "in format: `{$set}(<PARAMETER>)`", 20
            );
        }

        $output = [];

        foreach ($lines as $lineno => $line)
        {
            if ($set === 'dictionary')
            {
                if (strpos($line, $arg) === false)
                {
                    throw new exception\parser(
                        "Expected dictionary divider: `{$arg}` in:\n".
                        err_lines($liens, $lineno), 30
                    );
                }

                list($k, $l) = explode($arg, $line, 2);
                $output[trim($k)] = trim($l);
                continue;
            }
            else
            {
                $output[] = trim($line);
            }
        }

        if ($set === 'array' || $set === 'dictionary')
        {
            $output = "unserialize('" . serialize($output) . "')";
        }
        else
        {
            if (!$arg)
            {
                $arg = " ";
            }

            $output = implode($arg, $output);
            $output = preg_replace("/[^\\\]'/", "\\'", $output);
            $output = "'{$output}'";
        }

        return $output;
    }

    /**
     * Find closed `::/let`.
     * --
     * @param string $line
     * --
     * @return boolean
     */
    protected static function find_close_let($line)
    {
        return preg_match('/^[ \t]*?::\\/let?$/', $line);
    }

    /**
     * Find `::print <content>` regions.
     * --
     * @param string $line
     * @param array  $sets
     * --
     * @return array [ string $line, boolean $break ]
     */
    protected static function find_print($line, array $sets)
    {
        if (preg_match('/^[ \t]*?::print ([a-z0-9_]+)$/', $line, $match))
        {
            if (isset($sets[$match[1]]))
            {
                return [$sets[$match[1]], true];
            }
            else
            {
                return ['', true];
            }
        }

        return [$line, false];
    }

    /**
     * Find open `::set`.
     * --
     * @param string $line
     * --
     * @return string Id if found, null otherwise.
     */
    protected static function find_open_set($line)
    {
        if (preg_match('/^[ \t]*?::set ([a-z0-9_]+)$/', $line, $match))
        {
            return trim($match[1]);
        }
    }

    /**
     * Find closed `::/set`.
     * --
     * @param string $line
     * --
     * @return boolean
     */
    protected static function find_close_set($line)
    {
        return preg_match('/^[ \t]*?::\\/set?$/', $line);
    }

    /**
     * Find close for import and extend: `::/extend` `::/import`.
     * --
     * @param string $line
     * --
     * @return boolean
     */
    protected static function find_extend_import_close($line)
    {
        if (preg_match('/^[ \t]*?::\/(import|extend)?$/', $line, $match))
        {
            return $match[1];
        }
    }

    /**
     * Find `::import` statements.
     * --
     * @param string $line
     * @param array  $extend
     * --
     * @return string Id is found, null otherwise.
     */
    protected static function find_import($line, array &$extend)
    {
        if (preg_match(
            '/^[ \t]*?::import ([a-z0-9_\.\/]+)(?: from ([a-z0-9_\.\/]+))?( do)?$/',
            $line, $match))
        {
            if (isset($match[2]) && $match[2] !== ' do')
            {
                $module = $match[1];
                $id = trim($match[2]);
                $open = isset($match[3]) ? true : false;
            }
            else
            {
                $module = false;
                $id = $match[1];
                $open = isset($match[2]) ? true : false;
            }

            $extend[$id] = [
                'file'   => $id,
                'open'   => $open,
                'module' => $module,
                'set'    => []
            ];

            return $id;
        }
    }

    /**
     * Find `::extend` statements.
     * --
     * @param string $line
     * @param array  $extend
     * --
     * @return string Id if found, null otherwise.
     */
    protected static function find_extend($line, array &$extend)
    {
        if (preg_match(
            '/^[ \t]*?::extend ([a-z0-9_\.\/]+) set ([a-z0-9_]+)( do)?$/',
            $line, $match))
        {
            $id = trim($match[1]);
            $extend[$id] = [
                'open' => isset($match[3]),
                'self' => trim($match[2]),
                'set'  => []
            ];

            return $id;
        }
    }

    /**
     * Find `::use` statements.
     * --
     * @param string $line
     * @param array  $uses
     * --
     * @throws mysli\tplp\exception\parser 10 Name is already previously declared.
     * --
     * @return string Id if found, null otherwise.
     */
    protected static function find_use($line, array &$uses)
    {
        // Find ::use vendor.package( -> name)?
        if (preg_match(
            '/^[ \t]*?::use ([a-z0-9\_\.]+)(?: \-\> ([a-z0-9_]+))?$/',
            $line, $match))
        {
            $use = $match[1];

            if (!isset($match[2]))
            {
                $as = substr($use, strrpos($use, '.')+1);
            }
            else
            {
                $as = $match[2];
            }

            if (isset($uses[$as]))
            {
                if ($uses[$as]['use'] !== $use)
                {
                    throw new exception\parser(
                        static::f_error_use($use, $as, $uses[$as]['debug']), 10
                    );
                }
            }
            else
            {
                $uses[$as] = [
                    'statement' => "use ".str_replace('.', '\\', $use)."\\__tplp as {$as};",
                    'use'       => $use
                ];
            }

            return $as;
        }
    }

    /**
     * Process translation tags `{@KEY}`.
     * --
     * @param string $line
     * --
     * @return string
     */
    protected static function find_translation($line)
    {
        return preg_replace_callback('/{(@.*?)}/',
            function ($match)
            {
                $parsed = static::parse_translation($match[1]);
                return "<?php echo {$parsed}; ?>";
            },
            $line
        );
    }

    /**
     * Process `::for` statement.
     * --
     * @param string $line
     * --
     * @return array [ string $line, boolean $opened ]
     */
    protected static function find_for($line)
    {
        $opened = false;
        $line = preg_replace_callback(
            '/::for ([a-zA-Z0-9\_]+\,\ ?)?([a-zA-Z0-9\_]+) in (.*?)'.
            '(?: set ([a-zA-Z0-9\_]+))?$/',
            function ($match) use (&$opened)
            {
                $key = '';

                try
                {
                    $key = static::parse_variable(trim($match[1], ', '));
                }
                catch (\Exception $e)
                {
                    // Pass
                }

                $val = static::parse_variable($match[2]);
                $var = static::parse_variable_with_functions($match[3]);
                $exp = $key ? "{$key} => {$val}" : $val;
                $opened = true;

                if (!isset($match[4]))
                {
                    return "<?php foreach (" . $var . ' as ' . $exp . '): ?>';
                }

                // If we have extended variable, e.g.:
                // pos[count], pos[current], pos[last], pos[first],
                // pos[odd], pos[even]
                $varname = $match[4];

                return "<?php".
                    "\n\${$varname} = [];".
                    "\n\$tplp_var_for_{$varname} = {$var};".
                    "\n\${$varname}['count'] = count(\$tplp_var_for_{$varname});".
                    "\n\${$varname}['current'] = 0;".
                    "\nforeach (\$tplp_var_for_{$varname} as {$exp}):".
                    "\n  \${$varname}['current']++;".
                    "\n  \${$varname}['first'] = (\${$varname}['current'] === 1);".
                    "\n  \${$varname}['last'] = ".
                        "(\${$varname}['current'] === \${$varname}['count']);".
                    "\n  \${$varname}['odd'] = !!(\${$varname}['current'] % 2);".
                    "\n  \${$varname}['even'] = !(\${$varname}['current'] % 2);".
                    "\n?>";
            },
            $line
        );

        return [$line, $opened];
    }

    /**
     * Process special tags:
     * `::continue`, `::break`, `::else`, `::/for` and `::/if`.
     * --
     * @param string $line
     * --
     * @return array [ string $line, string $type ]
     */
    protected static function find_special_tags($line)
    {
        $type = '';
        $line = preg_replace_callback(
            '#::(else|break|continue|/if|/for)#',
            function ($match) use (&$type)
            {
                switch ($match[1])
                {
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
            },
            $line
        );

        return [$line, $type];
    }

    /**
     * Process logical expression: `var or var1 ...`.
     * --
     * @param string $expression
     * --
     * @return string
     */
    protected static function parse_logical_expression($expression)
    {
        return preg_replace_callback(
            '/(\\|\\| |&& |OR |AND )?'. // ) and ...
            '(\\(?\\ ?not \\(?|[\ !(]*)'. //  not var ... !(var ... !var
            '(.*?)'. // variable
            // and ... or ... )*
            '( != | !== | === | == | &lt; | < | &gt; | > | <= | &lt;= '.
            '| >= | &gt;= | \\|\\| | && | OR | AND |[\\)\\ ]{1,}|$)/i',
            function ($match)
            {
                if (implode('', $match) === '')
                {
                    return;
                }

                // logical before and, or...
                $logical_before = $match[1];

                // Special characters: (, !
                $mod = str_replace([' ', 'not'], ['', '!'], $match[2]);

                // Variable / function / boolean, numeric, null
                $variable = $match[3];

                // AND, OR, !=, ==, <, >, <=, >=
                $logical = str_replace(
                    ['&lt;', '&gt;'],
                    ['<', '>'],
                    $match[4]
                );
                $logical = str_replace(' ', '', $logical);

                if (substr($logical, 0, 1) !== ')')
                {
                    $logical = ' ' . $logical;
                }

                if ($variable)
                {
                    $variable = static::parse_variable_with_functions($variable);
                }

                return trim($logical_before . $mod . $variable . $logical) . ' ';
            },
            $expression
        );
    }

    /**
     * Find inline if `{var if var else 'No-var'}`.
     * --
     * @param string $line
     * --
     * @return string
     */
    protected static function find_inline_if($line)
    {
        return preg_replace_callback(
            '/{(.*?) if (.*?)(?: else (.*?))?}/',
            function ($match)
            {
                $var[0] = trim($match[1]);

                if (substr($var[0], 0, 1) === '@')
                {
                    $var[0] = static::parse_translation($var[0]);
                }
                else
                {
                    $var[0] = static::parse_variable_with_functions($var[0]);
                }

                $var[1] = isset($match[3]) ? trim($match[3]) : '';

                if ($var[1])
                {
                    if (substr($var[1], 0, 1) === '@')
                    {
                        $var[1] = static::parse_translation($var[1]);
                    }
                    else
                    {
                        $var[1] = static::parse_variable_with_functions($var[1]);
                    }
                }

                $expression = trim(static::parse_logical_expression($match[2]));

                return "<?php echo ({$expression}) ? {$var[0]} : " .
                    ($var[1] ? $var[1] : "''") . "; ?>";
            },
            $line
        );
    }

    /**
     * Process `::if` and `::elif`.
     * --
     * @param string $line
     * --
     * @return array [ string $line, boolean $opened ]
     */
    protected static function find_if($line)
    {
        $opened = false;
        $logical =
        $line = preg_replace_callback(
            '/::(if|elif) (.*)/',
            function ($match) use (&$opened)
            {
                $expression = static::parse_logical_expression($match[2]);
                $type = ($match[1] === 'elif' ? 'elseif' : 'if');
                // Line and opened status
                $opened = ($type === 'if');

                return '<?php ' . $type . ' (' . trim($expression) . '): ?>';
            },
            $line
        );

        return [$line, $opened];
    }

    /**
     * Find variables and functions.
     * Variables: {var}, {var[key]}, {var->prop}
     * Functions: {var|func}, {var|func:var,'param'},
     * {var|ns/method:var,'param'}
     * --
     * @param string $line
     * --
     * @throws mysli\tplp\exception\parser 12 Not a valid variable (empty): ``.
     * --
     * @return string
     */
    protected static function find_var_and_func($line)
    {
        $line = preg_replace_callback(
            '/\{(?=[^@])(.*?)\}/',
            function ($match)
            {
                // no echo {((variable))}
                if (substr($match[1], 0, 2) === '((' &&
                    substr($match[1], -2) === '))')
                {
                    $match[1] = substr($match[1], 2, -2);
                    $echo = '';
                }
                else
                {
                    $echo = 'echo ';
                }

                $var = static::parse_variable_with_functions(trim($match[1]));

                if (trim($var) === '')
                {
                    throw new exception\parser(
                        "Not a valid variable (empty): ``.", 12
                    );
                }

                return '<?php ' . $echo . $var . '; ?>';
            },
            $line
        );

        return $line;
    }

    /**
     * Process block regions like: `{{{` and `{*`.
     * --
     * @param string  $line
     * @param integer $lineno
     * @param array   $block
     * --
     * @return null
     */
    protected static function find_block_regions(&$line, $lineno, array &$block)
    {
        // {{{
        while (strpos($line, '{{{') !== false)
        {
            list($line, $block['contents']) = explode('{{{', $line, 2);
            $block['start'] = $lineno;
            $block['write'] = true;
            $block['close'] = '}}}';

            if (strpos($block['contents'], '}}}') !== false)
            {
                list($blockoff, $lineoff) = explode(
                    '}}}', $block['contents'], 2
                );
                $line = $line . $lineoff;
                $block['contents'] = $block['contents'] . $blockoff;
                $block['close'] = '';
                $block['start'] = 0;
            }
        }

        // {*
        while (strpos($line, '{*') !== false)
        {
            list($line, $commenton) = explode('{*', $line, 2);
            $block['start'] = $lineno;
            $block['write'] = false;
            $block['close'] = '*}';

            if (strpos($commenton, '*}') !== false)
            {
                list($_, $lineoff) = explode('*}', $commenton, 2);
                $line = $line . $lineoff;
                $block['close'] = '';
                $block['start'] = 0;
            }
        }
    }

    /**
     * Process end block like: `*}` and `}}}`.
     * --
     * @param string $line
     * @param array  $block
     * --
     * @return string
     */
    protected static function find_end_block(&$line, array &$block)
    {
        $output = false;

        if ($block['close'] && strpos($line, $block['close']) !== false)
        {
            list($blockoff, $line) = explode($block['close'], $line, 2);

            if ($block['write'])
            {
                $output = rtrim($block['contents'] . $blockoff);
            }

            $block['contents'] = '';
            $block['start']    = 0;
            $block['close']    = '';
            $block['write']    = false;
        }

        return $output;
    }

    /**
     * Process escaped curly brackets, e.g.: `\{` and `\}`.
     * --
     * @param string  $line
     * @param boolean $protect
     * --
     * @return string
     */
    protected static function escape_curly_brackets($line, $protect)
    {
        if ($protect)
        {
            $line = str_replace('\\{', '--MYSLI-CB-OPEN', $line);
            $line = str_replace('\\}', '--MYSLI-CB-CLOSE', $line);
        }
        else
        {
            $line = preg_replace('/--MYSLI-CB-OPEN/',  '{',  $line);
            $line = preg_replace('/--MYSLI-CB-CLOSE/', '}',  $line);
        }

        return $line;
    }

    /**
     * Helper method to escape single quotes.
     * --
     * @param string $match
     * --
     * @return string
     */
    protected static function escape_single_quotes_in($match)
    {
        return preg_replace_callback(
            "/'(.*?)'/",
            function ($match)
            {
                return '--MYSLI-QUOT-ST-'.
                    base64_encode($match[1]).
                    '-MYSLI-END-QUOT';
            },
            $match
        );
    }

    /**
     * Protect things wrapped in `{''}`.
     * --
     * @param string  $line
     * @param boolean $protect Protection on/off.
     * --
     * @return string
     */
    protected static function escape_single_quotes($line, $protect)
    {
        if ($protect)
        {
            // Protect everything prefixed with ::''
            $line = preg_replace_callback(
                "/^([ \t]*?::[a-z]*? .*?)$/",
                function ($match)
                {
                    return static::escape_single_quotes_in($match[1]);
                },
                $line
            );

            // Protect everything wrapped in {''}
            $line = preg_replace_callback(
                "/{(.*?)}/",
                function ($match)
                {
                    return '{'.static::escape_single_quotes_in($match[1]).'}';
                },
                $line
            );
        }
        else
        {
            // Restore everything wrapped in ''
            $line = preg_replace_callback(
                "/--MYSLI-QUOT-ST-(.*?)-MYSLI-END-QUOT/",
                function ($match)
                {
                    return '\'' . base64_decode($match[1]) . '\'';
                },
                $line
            );
        }

        return $line;
    }

    /**
     * Parse translation in format: `@TRANSLATION(count) var`.
     * --
     * @param string $string
     * --
     * @return string
     */
    protected static function parse_translation($string)
    {
        return preg_replace_callback(
            '/^@([A-Z0-9_]+)(?:\((.*?)\))?(.*?)$/',
            function ($match)
            {
                $key = trim($match[1]);
                $plural = trim($match[2]);

                if (!is_numeric($plural))
                {
                    $plural = static::parse_variable_with_functions($plural);
                }

                // Process variables
                $variables = explode(',', trim($match[3]));

                foreach ($variables as &$var)
                {
                    try
                    {
                        $var = static::parse_variable(trim($var));
                    }
                    catch (\Exception $e)
                    {
                        if ($e->getCode() !== 10)
                        {
                            throw $e;
                        }
                        else
                        {
                            $var = "''";
                        }
                    }
                }

                $variables = implode(', ', $variables);
                $variables = trim($variables, "'") ? ', [' . $variables . ']' : '';

                // Do we have plural?
                $key = $plural ? "['$key', $plural]" : "'{$key}'";

                return '$tplp_func_translator_service(' . $key . $variables . ')';
            },
            $string
        );
    }

    /**
     * Process variable + function: `var|func|func`.
     * --
     * @param string $line
     * --
     * @throws mysli\tplp\exception\parser
     *         10 Function require argument in format `arg|func`...
     * --
     * @return string
     */
    protected static function parse_variable_with_functions($line)
    {
        $line = trim($line);

        // coud be 0
        if (!$line)
        {
            return $line;
        }

        // Check if there's variable
        if (substr($line, 0, 1) === '|')
        {
            $has_var = false;
            $line = substr($line, 1);
        }
        else
        {
            $has_var = true;
        }

        $segments = explode('|', $line);

        if ($has_var)
        {
            $variable = array_shift($segments);
            $variable = static::parse_variable($variable);
        }

        $processed = '%seg';
        $segments = array_reverse($segments);

        foreach ($segments as $segment)
        {
            $processed = str_replace(
                '%seg', static::parse_functions($segment), $processed
            );
        }

        if (!$has_var)
        {
            if (preg_match('/\\$?[a-z0-9\\:_]+\\(%seg/i', $processed, $m))
            {
                $m = explode("(", $m[0], 2)[0];

                if (substr($m, 0, 1) !== '$' && !strpos($m, '::'))
                {
                    throw new exception\parser(
                        "Function require argument in format `arg|func`: `{$m}`",
                        10
                    );
                }
            }

            return str_replace(['%seg, ', '%seg'], '', $processed);
        }
        else
        {
            return str_replace('%seg', $variable, $processed);
        }
    }

    /**
     * Convert variables to PHP format.
     * Example:
     *
     *     something[key]->property // => $something['key']->property
     *
     * Must be valid variable format! Return unchanged:
     * numbers, true, false, null...
     * --
     * @param string $variable
     * --
     * @throws mysli\tplp\exception\parser
     *         10 Not a valid variable (empty): ``.
     *
     * @throws mysli\tplp\exception\parser
     *         20 Variable name cannot start with `$`...
     *
     * @throws mysli\tplp\exception\parser
     *         21 Not a valid variable name...
     *
     * @throws mysli\tplp\exception\parser
     *         22 Variable name cannot start with number...
     * --
     * @return string
     */
    protected static function parse_variable($variable)
    {
        $variable = trim($variable);

        // Check if we have valid variable
        if ($variable === '')
            throw new exception\parser("Not a valid variable (empty): ``", 10);

        if (substr($variable, 0, 8) === '--MYSLI-') {
            return $variable;
        }

        if (is_numeric($variable))
        {
            return $variable;
        }

        if (in_array($variable, ['true', 'false', 'null', "''"]))
        {
            return $variable;
        }

        // Encoded string!
        if (substr($variable, 0, 1) === '-' && is_numeric($variable))
        {
            return $variable;
        }

        $variable = preg_replace_callback(
            '/\[(.*?)\]/',
            function ($match)
            {
                if (substr($match[1], 0, 1) === '\\')
                {
                    return '[' . substr($match[1], 1) . ']';
                }
                else
                {
                    return "['{$match[1]}']";
                }
            },
            $variable
        );

        if (substr($variable, 0, 1) === '$')
        {
            throw new exception\parser(
                "Variable name cannot start with `$`: `{$variable}`", 20
            );
        }

        if (!preg_match(
            '/^[a-z0-9_]+((\\-\\>[a-z0-9_]+)|(\\[\'.+\'\\]))?$/i', $variable))
        {
            throw new exception\parser(
                "Not a valid variable name: `{$variable}`", 21
            );
        }

        if (is_numeric(substr($variable, 0, 1)))
        {
            throw new exception\parser(
                "Variable name cannot start with number: `{$variable}`", 22
            );
        }

        return '$' . $variable;
    }

    /**
     * Convert functions to PHP format.
     * --
     * @param string $function
     * --
     * @throws mysli\tplp\exception\parser
     *         10 Missing parameter.
     *
     * @throws mysli\tplp\exception\parser
     *         20 Function name cannot start with number...
     *
     * @throws mysli\tplp\exception\parser
     *         21 Not a valid function name...
     *
     * @throws mysli\tplp\exception\parser
     *         22 Function is not valid - too many segments.
     * --
     * @return string
     */
    protected static function parse_functions($function)
    {
        // Put function to meaningful pieces
        $function = trim($function);
        $segments = explode(':', $function);
        $function = trim(array_shift($segments));

        // Check if we any have segments...
        if (!isset($segments[0]))
        {
            $segments = [];
        }
        else
        {
            $segments = explode(',', $segments[0]);
        }

        // Process segments (parameters)
        foreach ($segments as $key => $segment)
        {
            try
            {
                $segments[$key] = static::parse_variable($segment);
            }
            catch (\Exception $e)
            {
                if ($e->getCode() !== 1) { throw $e; }
            }
        }

        // If it's one of the native function, then we'll set it as such...
        if (isset(static::$functions[$function]))
        {
            $function = static::$functions[$function];

            if (strpos($function, '%1') !== false)
            {
                foreach ($segments as $key => $segment)
                {
                    if (strpos($function, '%' . ($key + 1)))
                    {
                        $function = str_replace(
                            '%' . ($key + 1), $segment, $function
                        );
                        unset($segments[$key]);
                    }
                }

                if (preg_match('/%[0-9]+/', $function))
                {
                    throw new exception\parser(
                        "Missing parameter: `{$function}`", 10
                    );
                }
            }
            return str_replace(
                ', ...',
                ($segments ? ', ' . implode(', ', $segments) : ''),
                $function
            );
        }

        if (is_numeric(substr($function, 0, 1)))
        {
            throw new exception\parser(
                "Function name cannot start with number: `{$function}`", 20
            );
        }

        if (!preg_match('/^[a-z0-9_\\/]+$/i', $function))
        {
            throw new exception\parser(
                "Not a valid function name: `{$function}`", 21
            );
        }

        // Imported static method call: blog/method => blog::method
        if (strpos($function, '/') !== false)
        {
            $sfunction = explode('/', $function);

            if (count($sfunction) !== 2)
            {
                throw new exception\parser(
                    "Function `{$function}` is not valid - too many segments", 22
                );
            }

            $function = implode('::', $sfunction);
        }
        else
        {
            // Variable function
            $function = '$tplp_func_' . $function;
        }

        // NOT a native function, nor method set it to be variable (func)...
        if (empty($segments))
        {
            return $function . '(%seg)';
        }
        else
        {
            return $function . '(%seg, ' . implode(', ', $segments) . ')';
        }
    }

    /**
     * Handle file import.
     * --
     * @param  string $file    Filename that needs to be imported.
     * @param  array  $uses
     * @param  array  $set
     * @param  string $module
     * @param  string $dir
     * @param  array  $replace
     * --
     * @return string
     */
    protected static function handle_import(
        $file, array $uses, $set, $module, $dir, $replace)
    {
        if (isset($replace[$file]))
        {
            if (is_array($replace[$file]))
                $path = implode('/', $replace[$file]);
            else
                $path = "{$dir}/{$file}.tpl.html";
        }
        else
            $path = static::resolve_relative_path($file, $dir);


        return static::parse(
            $path, $uses, $set, $module, $replace
        );
    }

    /**
     * Resolve relative filename for inclusions.
     * --
     * @param string $relative
     * @param string $dir
     * --
     * @throws mysli\tplp\exception\tplp 10 File not found...
     * --
     * @return string
     */
    protected static function resolve_relative_path($relative, $dir)
    {
        $path = realpath(fs::ds($dir, $relative.'.tpl.html'));

        if (!file::exists($path))
            throw new exception\parser("File not found: `{$path}`", 10);

        return $path;
    }

    /**
     * Format generic use exception message.
     * --
     * @param string $use
     * @param string $as
     * @param array  $previous_debug
     * --
     * @return string
     */
    protected static function f_error_use($use, $as, array $previous_debug)
    {
        $return = "Cannot use `{$use}` as `{$as}` because the name ".
        "is already previously declared in following location(s):\n";

        foreach ($previous_debug as $previous)
        {
            $return .= $previous['lines']."File `{$previous['file']}`\n\n";
        }

        $return .= "ERROR:";

        return $return;
    }

    /*
    --- Properties -------------------------------------------------------------
     */

    protected static $functions = [
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
        "slice"         => "( is_array(%seg) ? array_slice(%seg, ...) ".
                                            ": substr(%seg, ...) )",
        "word_wrap"     => "wordwrap(%seg, %1, '<br/>')",
        "max"           => "max(%seg, ...)",
        "min"           => "min(%seg, ...)",
        "column"        => "array_column(%seg, %1, ...)",
        "reverse"       => "( is_array(%seg) ? array_reverse(%seg) ".
                                            ": strrev(%seg) )",
        "contains"      => "( (is_array(%seg) ? in_array(%1, %seg) ".
                                            ": strpos(%seg, %1)) !== false )",
        "key_exists"    => "array_key_exists(%1, %seg)",
        "sum"           => "array_sum(%seg)",
        "unique"        => "array_unique(%seg)",
        "range"         => "range(%1, %2, ...)",
        "random"        => "rand(%1, %2)"
    ];
}
