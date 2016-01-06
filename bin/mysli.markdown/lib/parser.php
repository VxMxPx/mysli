<?php

/**
 * Parse Markdown string.
 * Note: There's \mysli\markdown\markdown available,
 * for static access to an instance of this class.
 */
namespace mysli\markdown; class parser
{
    const __use = '
        .{ lines, output, exception.parser }
        mysli.toolkit.type.{ str, arr }
    ';

    // Where
    const flow_after   = 'after';
    const flow_before  = 'before';
    const flow_loop    = 'loop';
    const flow_replace = 'replace';

    /**
     * Which elements can be contained inside <p> tag.
     * --
     * @var array
     */
    protected static $p_allow = [
        'img', 'em', 'strong', 'i', 'b', 'sup', 'sub', 'del', 'ins'
    ];

    /**
     * How lines should be processed, this allows plugging costume parser(s).
     * Please note, that `before` and `after` will be run once, before and
     * after the main loop.
     * --
     * @var array
     */
    protected $flow = [
        'before' => [],
        'loop'   => [
            'self::do_blockquote',
            'self::do_list',
            'self::do_code',
            'self::do_entities',
            'self::do_header',
        ],
        'after'  => [
            'self::do_paragraph'
        ]
    ];

    /**
     * Input markdown broken to lines.
     * --
     * @var array
     */
    protected $markdown;

    /**
     * Markdown source in lines.
     * --
     * @var \mysli\markdown\lines
     */
    protected $lines;

    /**
     * Default options to be extended by user.
     * --
     * @var array
     * --
     * @opt boolean allow_html Weather HTML is allowed when processing Markdown.
     */
    protected $options = [
        'allow_html'  => true
    ];

    /**
     * Construct parser.
     * --
     * @param string $markdown
     * @param array  $options  (see: static::$options)
     */
    function __construct($markdown, array $options=[])
    {
        $this->markdown = explode("\n", str::to_unix_line_endings($markdown));
        $this->set_options($options);
    }

    /**
     * Set option(s).
     * --
     * @example $output->set_options(['allow_html' => false]);
     * --
     * @param array $options (see: static::$options)
     */
    function set_options(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Run process and return output.
     * --
     * @return mysli\markdown\output
     */
    function process()
    {
        // (Re)Set lines
        $this->lines = new lines($this->markdown);

        // Start main loop
        $this->loop(0, $this->lines->count());

        return new output($this->lines);
    }

    /**
     * Start the main loop.
     * --
     * @param integer $start_at
     * @param integer $limit
     * --
     * @throws mysli\markdown\exception\parser ... ...
     */
    protected function loop($start_at, $limit)
    {
        foreach (['before', 'loop', 'after'] as $type)
        {
            for ($i=$start_at; $i < $limit; $i++)
            {
                try
                {
                    if (!static::flow($this->flow, $i, $type)) { continue; }
                }
                catch (\Exception $e)
                {
                    throw new exception\parser(
                        f_error($this->markdown, $i, $e->getMessage(), null)
                    );
                }
            }
        }
    }

    // Flow --------------------------------------------------------------------

    /**
     * Allows you to control parser flow. Basically you can replace internal
     * methods, with your own, or insert your own before or after internals.
     * --
     * @example $parser->set_flow(function ($at, $lines) {
     *     // Step by step:
     *     // - Add this function to the flow,
     *     // - add it to the main loop,
     *     // - add it before ...
     *     // - ... the `do_header` tag.
     * }, parser::flow_loop, parser::flow_before, 'self::do_header');
     * --
     * @param mixed $call
     *        Method (or function) to be called; two parameters will be send:
     *        integer               $at    current position in lines
     *        \mysli\markdown\lines $lines all lines
     *
     *        Expected return value is either:
     *        numeric which will break loop, and jump to particular position.
     *        Jump will be relative to return value, e.g. if return is 1,
     *        it will jump to +1 line.
     *
     *        Any other @retrun will be ignored and loop will continue normally.
     *
     * @param string $where
     *        Where to put the method:
     *        parser::flow_before before main loop will start
     *        parser::flow_loop   in main loop
     *        parser::flow_after  after main loop will ended
     *
     * @param string $position
     *        Position to which call should be inserted:
     *        parser::flow_before  at the beginning of list or before
     *                             particular tag (if $tag provided).
     *        parser::flow_after   at the end of list or after
     *                             particular tag (if $tag provided).
     *        parser::flow_replace replace tag, in this case $tag is required
     *
     * @param string $tag
     *        Tag is currently set method, (see: static::$flow) if used,
     *        costume method will be inserted before, after or it will
     *        replace internal. An example of tag would be: self::do_header
     * --
     * @throws mysli\markdown\exception\parser
     *         10 Invalid argument's value.
     *
     * @throws mysli\markdown\exception\parser
     *         20 Tag is not set.
     *
     * @throws mysli\markdown\exception\parser
     *         30 Cannot replace when no $tag is provided.
     *
     * @throws mysli\markdown\exception\parser
     *         40 Invalid argument's value.
     */
    function set_flow(
        $call, $where=parser::flow_loop, $position=parser::flow_after, $tag=null)
    {
        if (!isset($this->flow[$where]))
        {
            throw new exception\parser(
                "Invalid argument's value: \$where: `{$where}`. Expected const: ".
                "parser::flow_after|parser::flow_before|parser::flow_loop", 10
            );
        }

        $target = &$this->flow[$where];

        if ($tag && !isset($target[$tag]))
        {
            throw new exception\parser(
                "Tag is not set: `{$tag}` in `{$where}`.", 20
            );
        }

        switch ($position) {
            case self::flow_after:
                if ($tag)
                {
                    $p = array_search($tag, $target);
                    arr::insert($target, $call, $p);
                }
                else
                {
                    $target[] = $call;
                }
                break;

            case self::flow_before:
                if ($tag)
                {
                    $p = array_search($tag, $target);
                    arr::insert($target, $call, $p-1);
                }
                else
                {
                    array_unshift($target, $call);
                }
                break;

            case self::flow_replace:
                if ($tag)
                {
                    $p = array_search($tag, $target);
                    $target[$p] = $call;
                }
                else
                {
                    throw new exception\parser(
                        "Cannot replace, when no `\$tag` is provided.", 30
                    );
                }
                break;

            default:
                throw new exception\parser(
                    "Invalid argument's value: \$position: `{$position}`. ".
                    "Expected const: parser::flow_after|parser::flow_before|".
                    "parser::flow_replace", 40
                );
        }
    }

    /**
     * Start the flow. This will control main loop, it will set position in list
     * (current line), and continue if needed (i.e. if return is false, main
     * loop will continue).
     * This is internal method, which is tightly connected to $this->loop()
     * --
     * @param array $flows
     *        List of methods to be executed in particular order;
     *        those can be internal methods (self::method) or
     *        external (\vendor\package\class::method).
     *
     * @param integer $i
     *        Current position in lines.
     *
     * @param string $type
     *        Flow type to be executed.
     * --
     * @return boolean
     */
    private function flow($flows, &$i, $type)
    {
        // Main loop
        foreach ($flows[$type] as $flow)
        {
            if (substr($flow, 0, 6) === 'self::')
            {
                $method = substr($flow, 6);
                $r = $this->{$method}($i, $this->lines);
            }
            else
            {
                list($obj, $funct) = explode(':', $flow, 2);
                $r = call_user_func_array([$obj, $funct], [$i, $this->lines]);
            }

            if (is_numeric($r))
            {
                $i = $i + $r;
                return false;
            }
        }

        return true;
    }

    // Tags --------------------------------------------------------------------

    /**
     * Find header tags.
     * --
     * @example Find:
     * # Hader 1
     * ## Header 2
     *
     * or...
     *
     * Header 1
     * ========
     *
     * Header 2
     * --------
     * --
     * @param integer               $at
     * @param \mysli\markdown\lines $lines
     * --
     * @return integer
     */
    protected function do_header($at, \mysli\markdown\lines $lines)
    {
        $line = $lines->get($at);

        // Regular style headers...
        if (preg_match('/^(\#{1,6}) (.*?)(?: [#]+)?$/', $line, $match))
        {
            $hl = strlen($match[1]);

            // Set lines...
            $lines->set($at, $match[2], "h{$hl}");

            return;
        }

        // Setext headers
        $line = $lines->get($at+1);
        if (preg_match('/^[\-|\=]+$/', $line, $match))
        {
            $hl = substr($match[0], 0, 1) === '=' ? '1' : '2';
            $title = $lines->get($at);

            // Set lines...
            $lines->set($at, $title, "h{$hl}");
            $lines->erase($at+1, true, true);
            $lines->set_attr($at+1, 'skip', true);

            return +2;
        }
    }

    /**
     * Find entities at particular line.
     * Convert & to &amp; etc...
     * --
     * @param integer               $at
     * @param \mysli\markdown\lines $lines
     */
    protected function do_entities($at, \mysli\markdown\lines $lines)
    {
        // Get line
        $line = $lines->get($at);

        // Convert &, but leave &copy; ...
        $line = preg_replace('/&(?![a-z]{2,11};)/', '&amp;', $line);

        // Convert < >, if allow HTML
        if ($this->options['allow_html'] &&
            !$lines->get_attr($at, 'convert-tags'))
        {
            $line = preg_replace('/<(?![\/a-z]{1,}(.*)>)/', '&lt;', $line);

            $line = preg_replace_callback(
                '/(\<[a-z]{1,}.*)?(>)/',
                function ($match) {
                    if ($match[1])
                    {
                        return $match[0];
                    }
                    else
                    {
                        return '&gt;';
                    }
                },
                $line
            );
        }
        else
        {
            $line = str_replace(['<', '>'], ['&lt;', '&gt;'], $line);
        }

        $lines->set($at, $line);

        return;
    }

    /**
     * Find blockquotes.
     * --
     * @example
     *     > This is a blockquote text to be replaced.
     * --
     * @param integer               $at
     * @param \mysli\markdown\lines $lines
     */
    protected function do_blockquote($at, \mysli\markdown\lines $lines)
    {
        $opened = false;

        do
        {
            $line = $lines->get($at);

            if (preg_match('/^(>\ {0,1})(\ {0,4}>?.*?)$/', $line, $match))
            {
                $line = $match[2];

                // Trim line if not more than 2 spaces ( safe to assume nothing is nested )
                if (substr($line, 0, 2) !== '  ' && substr($line, 0, 1) !== "\t")
                {
                    $line = trim($line);
                }

                $lines->set($at, $line, [(!$opened ? 'blockquote' : false), false]);
                $lines->set_attr($at, 'in-blockquote', true);

                $opened = true;
            }
            else
            {
                // If it wasn't open, and we do have a new (non-empty) line,
                // then break out of loop.
                if ((!$opened && trim($line)) ||
                    $lines->get_attr($at, 'in-blockquote'))
                {
                    break;
                }
            }

            $at++;

        // Until there's next line...
        } while($lines->has($at));

        // If we had anything, close it
        if ($opened)
        {
            $lines->set_tag($at-1, [false, 'blockquote']);
        }

        return;
    }

    /**
     * Find lists.
     * --
     * @example
     *     - List Item
     *     - List Item
     *     - ...
     * --
     * @param integer               $at
     * @param \mysli\markdown\lines $lines
     */
    protected function do_list($at, \mysli\markdown\lines $lines)
    {
        $indent = 0;
        $opened = false;

        $list_item_regex = '/^([\ |\t]*)([\*|\+|\-]|[0-9]+\.)([^\-|\*|\+].+)$/';

        do
        {
            $line = $lines->get($at);

            if (preg_match($list_item_regex, $line, $match))
            {
                $line = $match[3];
                $indent_now = $this->indent_to_int($match[1]);

                if (!$opened) // New list being opened
                {
                    $indent = $indent_now;
                    $opened = true;

                    $type = in_array($match[2], ['*', '-', '+']) ? 'ul' : 'ol';

                    // Open list
                    $lines->set_tag($at, [$type, false]);

                    // Sub list?
                    if ($lines->get_attr($at, 'is-sub-list'))
                    {
                        $lines->erase_tag($at-1, '/li', 1);
                        // Not anymore
                        $lines->set_attr($at, 'is-sub-list', false);
                    }
                }
                else // This is an existent list
                {
                    // Close previous, if not already closed
                    if (!$lines->get_attr($at, 'is-sub-list'))
                    {
                        // Put closing tag into the last line with content
                        $cn = 1;
                        do
                        {
                            if (!$lines->is_empty($at-$cn, true))
                            {
                                $lines->set_tag($at-$cn, [false, 'li']);
                                break;
                            }
                            else $cn++;

                        } while ($lines->has($at-$cn));
                    }

                    // We have shorter indent
                    if ($indent_now < $indent)
                    {
                        break;
                    }
                    elseif ($indent_now > $indent)
                    {
                        // Insert some of indent back in
                        $line =
                            substr($match[1], $indent) .
                            $match[2] .
                            $line;

                        // This will be sub-list
                        $lines->set_attr($at, 'is-sub-list', true);
                    }
                    else
                    {
                        // If it was sub-list, it's not anymore
                        $lines->set_attr($at, 'is-sub-list', false);
                    }
                }

                // Open li... if not already opened!
                if (!$lines->get_attr($at, 'is-sub-list'))
                {
                    // All those tags already exists if it's sub-list
                    $lines->set($at, $line, ['li', false]);
                    $lines->set_attr($at, 'in-list-item', true);
                }
                else
                {
                    $lines->set($at, $line, false);
                }
                $lines->set_attr($at, 'list-type', $type);
            }
            else // !preg_match
            {
                // Not found, and still no match invalid line, get out!
                if (!$opened)
                {
                    // break; //!?
                    return;
                }

                if (!trim($line))
                {
                    // Empty line, tell it to stay in list, and continue...
                    $lines->set_attr($at, ['in-list-item' => true]);
                    $lines->set($at, trim($line));
                }
                else if (preg_match('/^([\ |\t]+)(.*?)$/', $line, $match))
                {
                    // Space less than current indent, that means we're breaking
                    if (strlen($match[1]) < $indent)
                    {
                        // Not spaced? || Was previous line empty?
                        if ($lines->is_empty($at-1, true) ||
                            $lines->get_attr($at, 'in-list-item'))
                        {
                            break;
                        }
                        else
                        {
                            $lines->set_attr($at, ['in-list-item' => true]);
                            $lines->set($at, trim($line));
                        }
                    }
                    else if (strlen($match[1]) > $indent)
                    {
                        $lines->set_attr($at, ['in-list-item' => true]);

                        $lines->set(
                            // $at, substr($match[1], $indent).$match[2], false
                            $at, trim($match[2]), false
                        );
                    }
                    else
                    {
                        $lines->set($at, $match[2], false);
                        $lines->set_attr($at, [
                            'in-list-item' => true
                        ]);
                    }
                }
                else
                {
                    // Not spaced? Was previous line empty?
                    if (!$lines->get($at-1))
                    {
                        break;
                    }
                    else
                    {
                        $lines->set_attr($at, [
                            'in-list-item' => true
                        ]);
                    }
                }
            }

            $at++;

        // Until there's next line...
        } while($lines->has($at));

        // Close ...
        if ($opened)
        {
            // Put closing tag into the last line with content
            $cn = 1;
            do
            {
                if (!$lines->is_empty($at-$cn, true))
                {
                    $lines->set_tag($at-$cn, [false, $type]);
                    break;
                }
                else $cn++;

            } while ($lines->has($at-$cn));
            // --- OR
            // $lines->set_tag($at-1, [false, $type]);

            // Put closing tag into the last line with content
            $cn = 1;
            do
            {
                if (!$lines->is_empty($at-$cn, true))
                {
                    if ($lines->get_attr($at-$cn, 'is-sub-list'))
                        $lines->set_tag($at-$cn, [false, 'li']);

                    break;
                }
                else $cn++;

            } while ($lines->has($at-$cn));
            // --- OR
            // if ($lines->get_attr($at-1, 'is-sub-list'))
            // {
            //     $lines->set_tag($at-1, [false, 'li']);
            // }

            // Put closing tag into the last line with content
            $cn = 1;
            do
            {
                if (!$lines->is_empty($at-$cn, true))
                {
                    if ($lines->get_attr($at-$cn, 'in-list-item'))
                        $lines->set_tag($at-$cn, [false, 'li']);

                    break;
                }
                else $cn++;

            } while ($lines->has($at-$cn));
            // --- OR
            // if ($lines->get_attr($at-1, 'in-list-item'))
            // {
            //     $lines->set_tag($at-1, [false, 'li']);
            // }
        }

        return;
    }

    /**
     * Find code block.
     * --
     * @example
     *     This is a code block to be replaced.
     * --
     * @param integer               $at
     * @param \mysli\markdown\lines $lines
     */
    protected function do_code($at, \mysli\markdown\lines $lines)
    {
        $opened = false;
        $start_at = $at;

        do
        {
            $line = $lines->get($at);

            if (preg_match('/^(\t|\ {4})(.*?)$/', $line, $match))
            {
                $line = $match[2];
                $lines->set($at, $line);
                $lines->set_attr($at, [
                    'in-code'      => true,
                    'lock-nl'      => true,
                    'lock-trim'    => true,
                    'no-indent'    => true,
                    'convert-tags' => true,
                    'no-process'   => true,
                ]);

                if (!$opened)
                {
                    $lines->set_tag($at, ['pre', false]);
                    $lines->set_tag($at, ['code', false]);
                }

                $opened = true;
            }
            else
            {
                // If it wasn't open, and we do have a new (non-empty) line,
                // then break out of loop.
                if ((!$opened && trim($line)) ||
                    $lines->get_attr($at, 'in-code'))
                {
                    break;
                }
            }

            $at++;

        // Until there's next line...
        } while($lines->has($at));

        // If we had anything, close it
        if ($opened)
        {
            $lines->set_tag($at-1, [false, 'pre']);
            $lines->set_tag($at-1, [false, 'code']);
            return $at - $start_at;
        }

        // Skip over checked lines
        return;
    }

    /**
     * Do paragraphs.
     * --
     * @param  integer               $at
     * @param  \mysli\markdown\lines $lines
     * --
     * @return integer
     */
    function do_paragraph($at, \mysli\markdown\lines $lines)
    {
        // *1. complete absence of tags should open if not already opened,
        //     if line is not for skip, and if line has any content
        // *2. unclosed li, when next line has no tags should open
        // *3. blockquote should always open, if no other tags follows
        // dump($lines->debug_flat_array());
        $opened = false;

        // OPENING
        do
        {
            // Tag as a string
            if (substr(trim($lines->get($at)), 0, 1) === '<')
            {
                $allow = implode('|', static::$p_allow);
                $regex = "/^\\<({$allow})(\\ +|\\>).*$/i";

                if (preg_match($regex, $lines->get($at)))
                {
                    $lines->set_tag($at, [ 'p', false ]);
                    $lines->set_attr($at, 'in-do_paragrap', true);
                    $opened = true;
                }
                else if ($opened)
                {
                    $lines->set_tag($at-1, [ false, 'p' ]);
                    $opened = false;
                    $at--;
                }

                $at++;
                continue;
            }

            if (!$opened)
            {
                if ($lines->get_attr($at, 'no-process'))
                {
                    $at++;
                    continue;
                }
                //
                // Complete absence of tags should open if not already opened.
                // If line is not for skip, and if line has any content.
                //
                else if (!$lines->has_tag($at) && trim($lines->get($at)))
                {
                    $lines->set_tag($at, [ 'p', false ]);
                    $lines->set_attr($at, 'in-do_paragrap', true);
                    $opened = true;
                }
                //
                // Unclosed li, when next line has no (open) tags should open.
                //
                else if
                    (
                        // (
                            $lines->get_tag($at, -1) === 'li'
                            && !$lines->has_tag($at, '/li')
                        // )
                        && !$lines->has_tag($at+1, 'li')
                        // && $lines->count_before_tag($at+1) > 0
                        // && $lines->count_before_attr($at+1, 'in-list-item') > 0
                    )
                {
                    $lines->set_tag($at, [ 'p', false ]);
                    $lines->set_attr($at, 'in-do_paragrap', true);
                    $opened = true;
                }
                //
                // Blockquote should always open, if no other tags follows.
                //
                else if ($lines->get_tag($at, -1) === 'blockquote')
                {
                    $lines->set_tag($at, [ 'p', false ]);
                    $lines->set_attr($at, 'in-do_paragrap', true);
                    $opened = true;
                }
            }
            else
            {
                //
                // ANY open tag, should close if opened
                //
                if ($lines->has_tag($at))
                {
                    $lines->set_tag($at-1, [ false, 'p' ]);
                    $at--;
                    $opened = false;
                }
                //
                // Empty line should close, if opened
                //
                else if (!trim($lines->get($at)))
                {
                    $lines->set_tag($at-1, [ false, 'p' ]);
                    $at--;
                    $opened = false;
                }
                //
                // Close li should close
                // /blockquote should close
                //
                else if
                    (
                        $lines->has_tag($at, -1, '/') === 'li'
                        || $lines->has_tag($at, -1, '/') === 'blockquote'
                    )
                {
                    $lines->set_tag($at, [ false, 'p' ]);
                    $opened = false;
                }
            }

            $at++;

        } while ($lines->has($at));

        //
        // EOF should close if opened.
        //
        if ($opened)
        {
            $lines->set_tag($at-1, [ false, 'p' ]);
        }

        return $at;
    }

    /*
    Helper methods
     */

    /**
     * Convert <space>|<tab> indent to integer.
     * --
     * @param string $indent
     * --
     * @return integer
     */
    protected function indent_to_int($indent)
    {
        $indent = str_replace("\t", '    ', $indent);
        return strlen($indent);
    }
}
