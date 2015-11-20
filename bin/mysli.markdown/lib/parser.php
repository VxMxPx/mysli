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
        'img', 'em', 'bold', 'i', 'b'
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
            '&do_blockquote',
            '&do_list',
            '&do_entities',
            '&do_header',
        ],
        'after'  => [
            '&do_paragraph'
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
     * }, parser::flow_loop, parser::flow_before, '&do_header');
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
     *        replace internal. An example of tag would be: &do_header
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
     *        those can be internal methods (&method) or
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
            if (substr($flow, 0, 1) === '&')
            {
                $method = substr($flow, 1);
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
        if ($this->options['allow_html'])
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
        $found = false;

        do
        {
            $line = $lines->get($at);

            if (preg_match('/^(>\ {0,4})(>?.*?)$/', $line, $match))
            {
                $line = $match[2];

                // Trim line if not more than 2 spaces ( safe to assume nothing is nested )
                if (substr($line, 0, 2) !== '  ')
                {
                    $line = trim($line);
                }

                $lines->set(
                    $at,
                    $line,
                    [
                        (!$found ? 'blockquote' : false),
                        false
                    ]
                );
                $lines->set_attr($at, 'in-blockquote', true);

                $found = true;
            }
            else
            {
                // If it wasn't open, and we do have a new (non-empty) line,
                // then break out of loop.
                if ((!$found && trim($line)) ||
                    $lines->get_attr($at, 'in-blockquote'))
                {
                    break;
                }
            }

            $at++;

        // Until there's next line...
        } while($lines->has($at));

        // If we had anything, close it
        if ($found)
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
        $found = false;

        do
        {
            $line = $lines->get($at);

            if (preg_match(
                '/^([\ |\t]*)([\*|\+|\-]|[0-9]+\.)([^\-|\*|\+].+)$/', $line, $match))
            {
                $line = $match[3];
                $indent_now = $match[1];

                if (!$found)
                {
                    $indent = $indent_now;
                    $found = true;

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
                else
                {
                    // Close previous, if not already closed
                    if (!$lines->get_attr($at, 'is-sub-list'))
                    {
                        $lines->set_tag($at-1, [false, 'li']);
                    }

                    // We have shorter indent
                    if (strlen($indent_now) < strlen($indent))
                    {
                        break;
                    }
                    elseif (strlen($indent_now) > strlen($indent))
                    {
                        // Insert some of indent back in
                        $line =
                            substr($indent_now, strlen($indent)) .
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
            else
            {
                // Not found, and still no match invalid line, get out!
                if (!$found)
                {
                    break;
                }

                if (!trim($line))
                {
                    // Empty line, tell it to stay in list, and continue...
                    $lines->set_attr($at, [
                        'in-list-item' => true
                    ]);
                }
                else if (preg_match('/^([\ |\t]+)(.*?)$/', $line, $match))
                {
                    // Space less than current indent, that means we're breaking
                    if (strlen($match[1]) < strlen($indent))
                    {
                        // Not spaced? || Was previous line empty?
                        if (!$lines->get($at-1) ||
                            $lines->get_attr($at, 'in-list-item')
                            )
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
                    else if (strlen($match[1]) > strlen($indent))
                    {
                        $lines->set_attr($at, [
                            'in-list-item' => true
                        ]);
                        $lines->set(
                            $at,
                            substr($match[1], strlen($indent)).$match[2],
                            false
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
        if ($found)
        {
            $lines->set_tag(
                $at-1,
                [
                    false, $type
                ]
            );

            if ($lines->get_attr($at-1, 'is-sub-list'))
            {
                $lines->set_tag($at-1, [false, 'li']);
            }

            if ($lines->get_attr($at-1, 'in-list-item'))
            {
                $lines->set_tag($at-1, [false, 'li']);
            }
        }

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
    function do_paragraph($at, $lines)
    {

        // OPENING
        if (!trim($lines->get($at)))
        {
            return;
        }

        if ($lines->get_attr($at, 'in-do_paragrap'))
        {
            return;
        }

        // *1. Complete absence of tags should open if not already opened,
        // if line is not for skip, and if line has any content
        if (!$lines->has_tag($at) && !$lines->get_attr($at, 'in-do_paragrap'))
        {
            $lines->set_tag($at, ['p', 0]);
            $lines->set_attr($at, 'in-do_paragrap', true);
            return;
        }

        // *2. Unclosed li, when next line has not tags should open
        if ($lines->get_tag($at, -1) === 'li' && !$lines->has_tag($at, '/li') &&
            !trim($lines->get($at+1)))
        {
            $lines->set_tag($at, ['p', 0]);
            $lines->set_attr($at, 'in-do_paragrap', true);
            return;
        }

        // *3. blockquote should always open, if no other tags follows
        if ($lines->get_tag($at, '-1') === 'blockquote')
        {
            $lines->set_tag($at, ['p', 0]);
            $lines->set_attr($at, 'in-do_paragrap', true);
            return;
        }

        // CLOSING

        // General rules:
        // 2. ANY open tag, should close if opened
        // 3. Empty line should close, if opened
        // 4. EOF should close if opened
        // Specific rules:
        // 2. Close li should close if opened
        // 4. /blockquote should close if opened
    }
}
