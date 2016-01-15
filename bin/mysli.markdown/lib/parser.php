<?php

/**
 * Parse Markdown string.
 * Note: There's \mysli\markdown\markdown available,
 * for static access to an instance of this class.
 */
namespace mysli\markdown; class parser
{
    const __use = '
        .{ lines, exception.parser }
        mysli.toolkit.type.{ str, arr }
    ';

    /**
     * Which elements can be contained inside other tags.
     * Lines with tags not on this list, will be skipped and not processed in
     * any way, until closing tag is found.
     * --
     * @var array
     */
    protected $contained = [
        'a', 'abbr', 'address', 'audio', 'b', 'br', 'button', 'caption', 'cite',
        'code', 'del', 'dfn', 'em', 'figcaption', 'figure', 'h1', 'h2', 'h3',
        'h4', 'h5', 'h6', 'hr', 'ins', 'img', 'sub', 'sup', 'small', 'time', 'video'
    ];

    /**
     * How lines should be processed, this allows plugging costume parser(s).
     * --
     * @var array
     */
    protected $process = [
        'self::proc_html_tags',
        'self::proc_blockquote',
        'self::proc_list',
        'self::proc_code_backtick',
        'self::proc_code_indent',
        'self::proc_entities',
        'self::proc_header',
        'self::proc_paragraph',
        'self::proc_core_elements',
        'self::proc_links',
        'self::proc_urls',
        'self::proc_typography',
        'self::proc_footnotes',
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
     * @var lines
     */
    protected $lines;

    /**
     * Construct parser.
     * --
     * @param string $markdown
     */
    function __construct($markdown)
    {
        $this->markdown = explode("\n", str::to_unix_line_endings($markdown));
    }

    /**
     * Return lines.
     * --
     * @return lines
     */
    function get_lines()
    {
        return $this->lines;
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

        $i = 0;

        // Blocks
        foreach ($this->process as $block)
        {
            if (substr($block, 0, 6) === 'self::')
            {
                $method = substr($block, 6);
                $r = $this->{$method}($i, $this->lines);
            }
            else
            {
                list($obj, $funct) = explode(':', $block, 2);
                $r = call_user_func_array([$obj, $funct], [$i, $this->lines]);
            }

            // Skip forward
            if (is_numeric($r)) $i = $r;

            // Break the loop
            if ($r === false) return;
        }

        return $this->lines;
    }

    // Inline ------------------------------------------------------------------

    /**
     * Discover inline tags, like: __bold__, **bold**, _italic_, *italic*
     */
    protected function proc_core_elements($at, lines $lines)
    {
        $regbag = [
            // Match Escaped
            '/\\\\([^\\\\])/'
            => function ($match)
            {
                return '<<BASE64:'.base64_encode(trim($match[1])).'>>';
            },

            // Match code
            '/(?<!`)(`+)(?!`)(.+?)(?<!`)\1(?!`)/'
            => function ($match)
            {
                return '<code><<BASE64:'.base64_encode(trim($match[2])).'>></code>';
            },

            // Match **bold**
            '/\*\*(?! |\t)(\**.*?\**)(?<! |\t)\*\*/'
            => '<strong>$1</strong>',

            // Match __bold__
            '/(?<![a-zA-Z0-9])__(?! |\\t)(_*.*?_*)(?<! |\\t)__(?![a-zA-Z0-9])/'
            => '<strong>$1</strong>',

            // Match *italic*
            '/\*(?! |\t)(\**.*?\**)(?<! |\t)\*/'
            => '<em>$1</em>',

            // Match _italic_
            '/(?<![a-zA-Z0-9])_(?! |\\t)(_*.*?_*)(?<! |\\t)_(?![a-zA-Z0-9])/'
            => '<em>$1</em>',

            // Match ~~strikethrough~~
            '/(?<!~)~~(?! |\t|~)(.*?)(?<! |\t|~)~~(?!~)/'
            => '<s>$1</s>',

            // Match ~sub~
            '/(?<!~)~(?! |\t|~)(.*?)(?<! |\t|~)~(?!~)/'
            => '<sub>$1</sub>',

            // Match ^sup^
            '/(?<!\^)\^(?! |\t|\^)(.*?)(?<! |\t|\^)\^(?!\^)/'
            => '<sup>$1</sup>',

            // Match ++inserted++
            '/(?<!\+)\+\+(?! |\t|\+)(.*?)(?<! |\t|\+)\+\+(?!\+)/'
            => '<ins>$1</ins>',

            // Match ==marked==
            '/(?<!=)==(?! |\t|=)(.*?)(?<! |\t|=)==(?!=)/'
            => '<mark>$1</mark>',

            // Restore Escaped
            '/<<BASE64:(.*?)>>/'
            => function ($match)
            {
                return base64_decode($match[1]);
            }
        ];

        $this->process_inline($regbag, $at, $lines);
    }

    /**
     * Make typographic corrections, like (c) (r) and quotes
     */
    protected function proc_typography($at, lines $lines)
    {
        $regbag = [
            '/(\(c\))/i'  => '&copy;',   // ©
            '/(\(r\))/i'  => '&reg;',    // ®
            '/(\(tm\))/i' => '&trade;',  // ™
            '/(\(p\))/i'  => '&sect;',   // §
            '/(\+\-)/'    => '&plusmn;', // ±
            '/(\.{2,})/'  => '&hellip;', // …
            '/(\-{3})/'   => '&mdash;',  // —
            '/(\-{2})/'   => '&ndash;',  // –

            '/(\!{3,})/' => '!!!',
            '/(\?{3,})/' => '???',
        ];

        $regbag_multi = [
            "/(?<=\s|^)(')([^\s].*?)(')(?<![\s$])/m" => '&lsquo;$2&rsquo;', // ‘...’
            "/(?<!\s|^)(')/m"                        => '&rsquo;',          // ...’.
            '/(?<=\s|^)(")([^\s].*?)(")(?<![\s$])/m' => '&ldquo;$2&rdquo;', // “...”
            '"'                                      => '&quot;',           // "...
        ];

        $this->process_inline($regbag, $at, $lines);
        $this->process_inline_multi($regbag_multi, $at, $lines);
    }

    /**
     * Discover inline links, like:
     * ![](/path/to/image.jpg), [Link](http://domain.tld "Title")
     */
    protected function proc_links($at, lines $lines)
    {
        $regbag = [
            '/(!)?\[(.*?)\]\((.*?)(?: *"(.*?)")?\)/' => function ($match)
            {
                $title = isset($match[4]) ? " title=\"{$match[4]}\"" : '';

                list($_, $_, $txt, $url) = $match;
                $url = str_replace('"', '%22', $url);

                if ($match[1] === '!')
                {
                    return "<img src=\"{$url}\" alt=\"{$txt}\"{$title} />";
                }
                else
                {
                    return "<a href=\"{$url}\"{$title}>{$txt}</a>";
                }
            },
        ];

        $this->process_inline($regbag, $at, $lines, [
            'html-tag-opened' => true,
            'html-tag-closed' => true
        ]);
    }

    /**
     * Convert URLs (http://domain.tld/...) to tags.
     * --
     */
    protected function proc_urls($at, lines $lines)
    {
        $regbag = [
            // URL
            '/([a-z0-9]+:\/\/.*?\.[a-z]+.*?)([.,;:]?(?>[\s"()\'\\{\\}\\[\\]]|$))/'
            => '<a href="$1">$1</a>$2',
            // Mail
            '/([a-z0-9._%+-]+@[a-z0-9][a-z0-9-]{1,61}[a-z0-9]\.[a-z]{2,})/'
            => '<a href="mailto:$1">$1</a>',
        ];

        $this->process_inline($regbag, $at, $lines, [
            'html-tag-opened' => true,
            'html-tag-closed' => true
        ]);
    }

    // Blocks ------------------------------------------------------------------

    /**
     * Loop though lines and discover HTML tags. If HTML is not allowed, then
     * escape tags, otherwise mark some lines to be skipped.
     * --
     * @param integer $at
     * @param lines   $lines
     * --
     * @return integer
     */
    protected function proc_html_tags($at, lines $lines)
    {
        $opened = [];
        $start_at = $at;

        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);
            $here = [
                'opened' => [],
            ];

            // Find (tags on this line)
            $tags = [];
            preg_match_all(
                '#\<(\/?)([a-z]+)[ |\>|\/]{1}#', $line, $tags, PREG_SET_ORDER);

            foreach ($tags as $tag)
            {
                list($_, $closed, $tag) = $tag;
                $closed = !!$closed;

                if ($closed)
                {
                    if (in_array($tag, $here['opened']))
                        unset($here['opened'][array_search($tag, $here['opened'])]);

                    if (in_array($tag, $opened))
                        unset($opened[array_search($tag, $opened)]);

                    if (empty($here['opened']))
                        $lines->set_attr($at, 'html-tag-closed', true);
                }
                else
                {
                    $opened[] = $tag;
                    $here['opened'][] = $tag;

                    $lines->set_attr($at, 'html-tag-opened', true);

                    if (!in_array($tag, $this->contained))
                    {
                        $lines->set_attr($at, [
                            'no-process'         => true,
                            'no-process-by-open' => true,
                            // 'lock-trim'          => true,
                            // 'no-indent'          => true,
                            // 'lock-nl'            => true,
                        ]);
                    }
                }
            }

            if (count($opened))
            {
                $lines->set_attr($at, 'in-html-tag', true);

                foreach ($opened as $tag)
                {
                    if (!in_array($tag, $this->contained))
                    {
                        // Need for later cleanup... :>
                        $lines->set_attr(
                            $at,
                            'html-opened-list',
                            $lines->get_attr($at, 'html-opened').'::'.$tag
                        );

                        $lines->set_attr($at, [
                            'no-process' => true,
                        ]);
                    }
                }
            }

            $at++;
        }

        // Cleanup, cannot find some opened tags...?
        if (count($opened))
        {
            foreach ($opened as $tag)
            {
                $at = $start_at;

                while ($lines->has($at))
                {
                    $openedlist = $lines->get_attr($at, 'html-opened-list');

                    if ($openedlist
                        && !$lines->get_attr($at, 'no-process-by-open'))
                    {
                        $p = false;

                        if (false !== ($p = strpos($openedlist, "::{$tag}")))
                        {
                            $openedlist =
                                substr($openedlist, 0, $p).
                                substr($openedlist, $p+strlen($tag)+2);

                            if (!trim($openedlist))
                            {
                                $lines->set_attr($at, [
                                    'html-opened-list'   => false,
                                    'no-process'         => false,
                                    'in-html-tag'        => false,
                                    // 'lock-trim'          => false,
                                    // 'no-indent'          => false,
                                    // 'lock-nl'            => false
                                ]);
                            }
                            else
                            {
                                $lines->set_attr($at, 'html-opened-list', $openedlist);
                            }
                        }
                    }

                    $at++;
                }
            }
        }
    }

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
     * @param integer $at
     * @param lines   $lines
     */
    protected function proc_header($at, lines $lines)
    {
        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            // Regular style headers...
            if (preg_match('/^(\#{1,6}) (.*?)(?: [#]+)?$/', $line, $match))
            {
                $hl = strlen($match[1]);

                // Set lines...
                $lines->set($at, $match[2], "h{$hl}");

                $at++;
                continue;
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

                $at+2;
            }

            $at++;
        }
    }

    /**
     * Find entities at particular line.
     * Convert & to &amp; etc...
     * --
     * @param integer $at
     * @param lines   $lines
     */
    protected function proc_entities($at, lines $lines)
    {
        while ($lines->has($at))
        {
            // Get line
            $line = $lines->get($at);

            // Convert &, but leave &copy; ...
            $line = preg_replace('/&(?![a-z]{2,11};)/', '&amp;', $line);

            if ($lines->get_attr($at, 'convert-tags')
                || (!$lines->get_attr($at, 'in-html-tag')
                    && !$lines->get_attr($at, 'html-tag-opened')
                    && !$lines->get_attr($at, 'html-tag-closed')))
            {
                $line = str_replace(['<', '>'], ['&lt;', '&gt;'], $line);
            }
            else
            {
                $line = preg_replace('/(\<(?![a-z]|\/[a-z]))/', '&lt;', $line);
            }

            $lines->set($at, $line);

            $at++;
        }
    }

    /**
     * Find blockquotes.
     * --
     * @example
     *     > This is a blockquote text to be replaced.
     * --
     * @param integer $at
     * @param lines   $lines
     */
    protected function proc_blockquote($at, lines $lines)
    {
        $indent = 0;
        $last_at = false;
        $last_empty = false;

        while($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process')
                || $lines->get_attr($at, 'in-html-tag'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            if (preg_match('/^[ \\t]*((>[ \\t]*)+)(.*?)$/', $line, $match))
            {
                list($_, $levels, $last, $line) = $match;
                $indent_now = substr_count($levels, '>');

                // Add front space if it would indicate code block...
                if (substr($last, 2, 4) === '    ' || substr($last, 2, 1) === "\t")
                {
                    $line = substr($last, 2).$line;
                }

                $lines->set($at, $line);

                if ($indent_now > $indent)
                {
                    while ($indent_now > $indent)
                    {
                        $lines->set_tag($at, ['blockquote', false]);
                        $indent++;
                    }
                }
                elseif ($indent_now < $indent)
                {
                    while ($indent_now < $indent)
                    {
                        $lines->set_tag($at, [false, 'blockquote'], false);
                        $indent--;
                    }
                }

                $lines->set_attr($at, 'in-blockquote', true);
                $indent = $indent_now;
                $last_empty = false;
                $last_at = $at;
            }
            elseif ($indent) // preg_match
            {
                if (!trim($line))
                {
                    $last_empty = true;
                }
                else
                {
                    if (!$last_empty && $last_at !== false)
                    {
                        $lines->set_attr($at, 'in-blockquote', true);
                        $lines->move_tags($last_at, $at, [false, true]);
                        $last_at = $at;
                    }
                    else
                    {
                        while ($indent)
                        {
                            $lines->set_tag($last_at, [false, 'blockquote'], false);
                            $indent--;
                        }
                        $last_at = false;
                    }
                }
            }

            $at++;
        }

        // If we had anything, close it
        while ($indent)
        {
            $lines->set_tag($last_at, [false, 'blockquote'], false);
            $indent--;
        }
    }

    /**
     * Find lists.
     * --
     * @example
     *     - List Item
     *     - List Item
     *     - ...
     * --
     * @param integer $at
     * @param lines   $lines
     */
    protected function proc_list($at, lines $lines)
    {
        $opened = [];
        $list_item_regex = '/^([\ |\t]*)([\*|\+|\-]|[0-9]+\.) +(.*?)$/';
        $indent_now = $indent = 0;
        $last_li = false;
        $last_empty = false;

        while ($lines->has($at))
        {
            // Skip if no process
            if ($lines->get_attr($at, 'no-process')
                || $lines->get_attr($at, 'in-html-tag'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            if (preg_match($list_item_regex, $line, $match))
            {
                $line = trim($match[3]);
                $lines->set($at, $line);
                $indent_now = $this->indent_to_int($match[1]);
                $type = in_array($match[2], ['*', '-', '+']) ? 'ul' : 'ol';
                $last_li = $at;
                $last_empty = false;

                // The list is not opened, should we open new list?
                if (empty($opened))
                {
                    $opened[] = [ $type, ($indent_now-$indent) ];
                    $indent = $indent_now;
                    // Open list
                    $lines->set_tag($at, [$type, false]);
                    $lines->set_tag($at, ['li', 'li']);
                }
                else
                {
                    // The list is not opened
                    if ($indent === $indent_now)
                    {
                        $lines->set_tag($at, ['li', 'li']);
                    }
                    else if ($indent_now > $indent)
                    {
                        $lines->erase_tag($at-1, '/li', 1);
                        $lines->set_tag($at, [ $type, false ]);
                        $lines->set_tag($at, [ 'li', 'li' ]);
                        $opened[] = [ $type, ($indent_now-$indent) ];
                        $indent = $indent_now;
                    }
                    else if ($indent_now < $indent)
                    {
                        $lines->set_tag($at, ['li', 'li']);

                        while($indent_now < $indent)
                        {
                            list($cltag, $clindent) = array_pop($opened);
                            $lines->set_tag($at-1, [false, $cltag], false);
                            $lines->set_tag($at-1, [false, 'li'], false);
                            $indent = $indent - $clindent;
                        }
                        if (empty($opened))
                            $last_li = false;

                        $indent = $indent_now;
                    }
                }
            }
            else if (!empty($opened)) // NOT preg_match
            {
                if (empty($line))
                {
                    $last_empty = true;
                }
                else if ($last_li !== false)
                {
                    $local_indent = in_array(substr($line, 0, 1), [' ', "\t"]);

                    if ($last_empty && !$local_indent)
                    {
                        $opened = array_reverse($opened);
                        $openedc = count($opened)-1;

                        foreach ($opened as $c => $close)
                        {
                            $lines->set_tag($last_li, [false, $close[0]], false);

                            if ($c < $openedc)
                            {
                                $lines->set_tag($last_li, [false, 'li'], false);
                            }
                        }

                        $opened  = [];
                        $indent_now = $indent = 0;
                        $last_li = false;
                        $last_empty = false;
                    }
                    else
                    {
                        $lines->set($at, trim($line));
                        $lines->move_tags($last_li, $at, [ false, true ]);
                        $last_li = $at;
                        $last_empty = false;
                    }
                }
                else
                {
                    $last_empty = false;
                }
            }

            $at++;
        }

        if (count($opened) && $last_li)
        {
            $opened = array_reverse($opened);
            $openedc = count($opened)-1;

            foreach ($opened as $c => $close)
            {
                $lines->set_tag($last_li, [false, $close[0]], false);

                if ($c < $openedc)
                {
                    $lines->set_tag($last_li, [false, 'li'], false);
                }
            }
        }
    }

    /**
     * Find code block.
     * --
     * @example
     *     This is a code block to be replaced.
     * --
     * @param integer $at
     * @param lines   $lines
     */
    protected function proc_code_indent($at, lines $lines)
    {
        $opened = false;
        $last_at = false;

        while($lines->has($at))
        {
            $line = $lines->get($at);

            if (preg_match('/^(\t|\ {4})(.*?)$/', $line, $match))
            {
                // Can't open while inside HTML tag
                if ($lines->get_attr($at, 'in-html-tag')
                    || $lines->get_attr($at, 'in-code'))
                {
                    $at++;
                    continue;
                }

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
                $last_at = $at;
            }
            else
            {
                if ($opened && trim($line))
                {
                    $lines->set_tag($last_at, [ false, 'pre' ]);
                    $lines->set_tag($last_at, [ false, 'code' ]);
                }
                $opened = false;
            }

            $at++;
        }

        if ($opened)
        {
            $lines->set_tag($last_at, [ false, 'pre' ]);
            $lines->set_tag($last_at, [ false, 'code' ]);
        }
    }

    /**
     * Find code block.
     * --
     * @example
     * ``` php
     *     This is a code block to be replaced.
     * ```
     * --
     * @param integer $at
     * @param lines   $lines
     */
    protected function proc_code_backtick($at, lines $lines)
    {
        $opened = false;
        $count = null; // Count of open ticks

        while($lines->has($at))
        {
            $line = $lines->get($at);

            if (!$opened)
            {
                // Can't open while inside HTML tag
                if ($lines->get_attr($at, 'in-html-tag'))
                {
                    $at++;
                    continue;
                }

                if (preg_match('/^(`{3,}) ?([a-z]*)$/', $line, $match))
                {
                    $count = strlen($match[1]);

                    $lines->set_attr($at, [
                        'in-code'      => true,
                        'lock-nl'      => true,
                        'lock-trim'    => true,
                        'no-indent'    => true,
                        'convert-tags' => true,
                        'no-process'   => true,
                    ]);

                    if ($match[2])
                    {
                        $lines->set_attr($at, [
                            'html-attributes' => [
                                'code' => [ 'class="language-'.trim($match[2]).'"' ],
                            ]
                        ]);
                    }

                    $lines->set($at, '');
                    $lines->set_tag($at, ['pre', false]);
                    $lines->set_tag($at, ['code', false]);

                    $opened = true;
                }
            }
            else
            {
                if (preg_match('/^`{'.$count.'}$/', $line, $match))
                {
                    $lines->set($at, '');
                    $lines->set_tag($at, [ false, 'pre' ]);
                    $lines->set_tag($at, [ false, 'code' ]);
                    $lines->set_attr($at, [
                        'no-process' => true,
                    ]);
                    $opened = false;
                }
                else
                {
                    $lines->set_attr($at, [
                        'in-code'      => true,
                        'lock-nl'      => true,
                        'lock-trim'    => true,
                        'no-indent'    => true,
                        'convert-tags' => true,
                        'no-process'   => true,
                    ]);
                }
            }

            $at++;
        }
    }

    /**
     * Do paragraphs.
     * --
     * @param integer $at
     * @param lines   $lines
     */
    protected function proc_paragraph($at, lines $lines)
    {
        $last_at = false;

        while ($lines->has($at))
        {
            if ($last_at === false)
            {
                if (
                    $lines->get_attr($at, 'no-process')
                    || (!$lines->get_attr($at, 'html-tag-opened')
                        && $lines->get_attr($at, 'html-tag-closed'))
                    || ($lines->has_tag($at)
                        && !in_array($lines->get_tag($at, -1), ['li', 'blockquote']))
                    || $lines->is_empty($at, true)
                    || $lines->has_tag($at, '/li')
                    || $lines->has_tag($at+1, 'li')
                )
                {
                    $at++;
                    continue;
                }

                $lines->set_tag($at, [ 'p', false ]);
                $last_at = $at;
            }
            else
            {
                if ($lines->has_tag($at) || $lines->is_empty($at, true))
                {
                    $lines->set_tag($last_at, [ false, 'p' ]);
                    $last_at = false;
                    $at--;
                }
                else if (in_array($lines->get_tag($at, -1, '/'), ['li', 'blockquote']))
                {
                    $lines->set_tag($at, [ false, 'p' ]);
                    $last_at = false;
                }
                else
                {
                    $last_at = $at;
                }
            }

            $at++;
        }

        if ($last_at !== false)
        {
            $lines->set_tag($last_at, [ false, 'p' ]);
        }
    }

    /**
     * Do footnotes.
     * --
     * @param integer $at
     * @param lines   $lines
     */
    protected function proc_footnotes($at, lines $lines)
    {
        // Find definitions first!

        // Replace definitions
    }

    // Helper methods ----------------------------------------------------------

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

    /**
     * Generic process inline elements.
     * --
     * @param array   $regbag
     * @param integer $at
     * @param lines   $lines
     * @param array   $attr Set line attributes if modified.
     */
    protected function process_inline(
        array $regbag, $at, lines $lines, array $attr=[])
    {
        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process')
                || $lines->get_attr($at, 'html-tag-opened'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);
            $lineprev = $line;

            foreach ($regbag as $regex => $replace)
            {
                $line = $this->replace_inline($line, $regex, $replace);
            }

            if ($lineprev !== $line)
            {
                $lines->set($at, $line);
                $lines->set_attr($at, $attr);
            }

            $at++;
        }
    }

    /**
     * Generic process inline elements. This will check multiple lines.
     * --
     * @param array   $regbag
     * @param integer $at
     * @param lines   $lines
     */
    protected function process_inline_multi(array $regbag, $at, lines $lines)
    {
        $buffer = [];

        while (true)
        {
            if (!$lines->has($at) || $lines->get_attr($at, 'no-process')
                || $lines->get_attr($at, 'html-tag-opened')
                || $lines->is_empty($at, true))
            {
                // Any buffer
                if (count($buffer))
                {
                    $buffmrg = implode("\n", $buffer);
                    $linesno  = array_keys($buffer);

                    foreach ($regbag as $find => $replace)
                    {
                        $buffmrg = $this->replace_inline($buffmrg, $find, $replace);
                    }

                    // Put modified lines
                    foreach (explode("\n", $buffmrg) as $pos => $buffline)
                    {
                        $lines->set($linesno[$pos], $buffline);
                    }

                    $buffer = [];
                }

                if (!$lines->has($at))
                {
                    return;
                }
            }
            else
            {
                $buffer[$at] = $lines->get($at);
            }

            $at++;
        }
    }

    /**
     * Flexible replacement.
     * --
     * @param string $line
     * @param mixed  $find
     * @param mixed  $replace
     * --
     * @return string
     */
    protected function replace_inline($line, $find, $replace=null)
    {
        if (is_callable($find))
        {
            return $find($line);
        }
        else if (is_array($find) || !in_array(substr($find, 0, 1), ['/', '#']))
        {
            return str_replace($find, $replace, $line);
        }
        else
        {
            if (is_callable($replace))
            {
                return preg_replace_callback($find, $replace, $line);
            }
            else
            {
                return preg_replace($find, $replace, $line);
            }
        }
    }
}
