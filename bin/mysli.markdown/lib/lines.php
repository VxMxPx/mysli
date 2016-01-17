<?php

/**
 * Helper, manage lines when processing Markdown.
 */
namespace mysli\markdown; class lines
{
    const __use = '
        .{ exception.parser }
        mysli.toolkit.type.{ arr }
    ';

    /**
     * Inputed lines.
     * --
     * @var array
     */
    protected $in = [];

    /**
     * Processed lines.
     * --
     * @var array
     */
    protected $lines = [];

    /**
     * Instance of lines.
     * --
     * @param array $lines Raw input of Markdown lines.
     */
    function __construct(array $lines)
    {
        $this->in = $lines;
        $this->reset();
    }

    /**
     * Return all processed lines.
     * --
     * @return array
     */
    function get_all()
    {
        return $this->lines;
    }


    /**
     * Get line's tag(s) at particular position.
     * --
     * @param integer $at
     * --
     * @return array
     *         [ array $open_tags, string $line, array $close_tags, array $attributes ]
     */
    function get_raw($at)
    {
        if (isset($this->lines[$at]))
        {
            return $this->lines[$at];
        }
        else
        {
            return [ [], null, [], [] ];
        }
    }

    /**
     * Reset processed lines (to be the same as when object was constructed).
     * If $at provided, reset only particular line.
     * --
     * @param integer $at
     */
    function reset($at=null)
    {
        if ($at !== null)
        {
            if ($this->has($at))
            {
                $this->lines[$at] = [
                    [], $this->in[$at], [], []
                ];
            }
        }
        else
        {
            $this->lines = [];

            // Reset live & empty output
            foreach ($this->in as $lineno => $line)
            {
                $this->lines[$lineno] = [
                    // Open tags
                    [],
                    // Current line
                    $line,
                    // Close tags
                    [],
                    // Attributes
                    []
                ];
            }
        }

    }

    /**
     * Get number of all lines.
     * --
     * @return integer
     */
    function count()
    {
        return count($this->in);
    }

    /**
     * Set line at particular position.
     * This allows you to set line's content and tag(s).
     * --
     * @param integer $at
     *        Positon where to set.
     *
     * @param string $line
     *        Line's content.
     *
     * @param mixed $tags
     *        Either:
     *        array  [string $open, string $close]
     *        string Both tags
     * --
     * @throws mysli\markdown\exception\parser
     *         10 Trying to set a non-existent line.
     */
    function set($at, $line, $tags=null)
    {
        if (isset($this->lines[$at]))
        {
            // Line itself
            $this->lines[$at][1] = $line;

            if ($tags)
            {
                $this->set_tag($at, $tags);
            }
        }
        else
        {
            throw new exception\parser(
                "Trying to set a non-existent line: `{$at}` to: `{$line}`", 10
            );
        }
    }

    /**
     * Get line at particular position.
     * This will return only line without tags. See $this->get_raw() to get
     * full array (with attributes, etc...) for a line.
     * --
     * @param integer $at
     * --
     * @return sting
     */
    function get($at, $trim=false)
    {
        if (isset($this->lines[$at]))
        {
            return $this->lines[$at][1];
        }
        else
        {
            return null;
        }
    }

    /**
     * There's no content on this line.
     * --
     * @param integer $at
     * @param boolean $trim Weather to trim line before checking.
     * --
     * @return boolean
     */
    function is_empty($at, $trim=false)
    {
        $line = $this->get($at);
        return !($trim ? trim($line) : $line);
    }

    /**
     * Does line exists at particulat position.
     * --
     * @param integer $at
     * --
     * @return boolean
     */
    function has($at)
    {
        return isset($this->lines[$at]);
    }

    /**
     * Erase line at particular position.
     * --
     * @param integer $at
     * @param boolean $fully Wipe all tags on the line too.
     */
    function erase($at, $fully=false)
    {
        if (isset($this->lines[$at]))
        {
            if ($fully)
            {
                $this->lines[$at] = [
                    [], '', [], []
                ];
            }
            else
            {
                $this->lines[$at][1] = '';
            }
        }
    }

    /**
     * Get line's attribute.
     * --
     * @param string $at
     * @param string $attr
     * @param mixed  $default
     * --
     * @return mixed
     */
    function get_attr($at, $attr, $default=null)
    {
        if (isset($this->lines[$at]) && isset($this->lines[$at][3][$attr]))
        {
            return $this->lines[$at][3][$attr];
        }
        else
        {
            return $default;
        }
    }

    /**
     * Set attribute for a line.
     * --
     * @param integer $at
     * @param mixed   $attr  string one value | array multiple
     * @param mixed   $value
     * --
     * @throws mysli\markdown\exception\parser
     *         10 Setting attribute for non-existent line.
     */
    function set_attr($at, $attr, $value=null)
    {
        if (isset($this->lines[$at]))
        {
            if (is_array($attr))
            {
                foreach ($attr as $attr_k => $attr_v)
                {
                    $this->lines[$at][3][$attr_k] = $attr_v;
                }
            }
            else
            {
                $this->lines[$at][3][$attr] = $value;
            }
        }
        else
        {
            if (is_array($attr)) $attr = implode(',', array_keys($attr));
            throw new exception\parser(
                "Setting attribute for non-existent line: `{$attr}` at `{$at}`", 10
            );
        }
    }

    /**
     * Set line's tag(s) at particular position.
     * --
     * @param integer $at
     *
     * @param mixed $tags
     *        `array [string $open, string $close]` | `string both`
     *
     * @param boolean $close_prepend
     *        Prepend closed tag, rather than append.
     *        This will make sense in most cases when setting both tags.
     * --
     * @throws mysli\markdown\exception\parser
     *         10 Trying to set tag on non-existent line.
     */
    function set_tag($at, $tags=null, $close_prepend=true)
    {
        if (isset($this->lines[$at]))
        {
            // Both the same
            if ($tags)
            {
                if (!is_array($tags))
                {
                    $tags = [$tags, $tags];
                }

                // Open tag
                if (isset($tags[0]) && $tags[0])
                {
                    $this->lines[$at][0][] = $tags[0];
                }

                // Close tag
                if (isset($tags[1]) && $tags[1])
                {
                    if ($close_prepend)
                    {
                        array_unshift($this->lines[$at][2], $tags[1]);
                    }
                    else
                    {
                        $this->lines[$at][2][] = $tags[1];
                    }
                }
            }
        }
        else
        {
            throw new exception\parser(
                "Trying to set tag on non-existent line: `".
                print_r($tags, true)."` at `{$at}`.",
                10
            );
        }
    }

    /**
     * Move tags from one line to another (this will erase tags in $from and
     * move them $to). Tags will be appened to $to.
     * --
     * @param  integer $from
     * @param  integer $to
     * @param  array  $target [ open, close ]
     * --
     * @throws mysli\markdown\exception\parser 10 Source not found.
     * @throws mysli\markdown\exception\parser 20 Target not found.
     */
    function move_tags($from, $to, array $target)
    {
        if (!$this->has($from))
            throw new exception\parser("Source not found: `{$from}`.", 10);
        if (!$this->has($to))
            throw new exception\parser("Target not found: `{$to}`.", 20);

        $this->lines[$to][2] = array_merge(
            $this->lines[$to][2],
            $this->lines[$from][2]
        );

        $this->lines[$from][2] = [];
    }

    /**
     * Get one tag at particular line, at particular position.
     * --
     * @param integer $at
     *        Tag at which Line.
     *
     * @param integer $from
     *        Position, which can be negative, for example:
     *        -1 to get last tag in the list.
     *
     * @param string $type
     *        Either null (open tag) or '/' to query close tag.
     * --
     * @return string
     */
    function get_tag($at, $from, $type=null)
    {
        list($o, $c) = $this->get_tags($at);
        $pool = $type ? $c : $o;
        $pool = array_slice($pool, $from, 1);

        return isset($pool[0]) ? $pool[0] : null;
    }

    /**
     * Get tag(s) for particular line.
     * --
     * @param integer $at
     * --
     * @return array [ array open, array close ]
     */
    function get_tags($at)
    {
        list($o, $_, $c, $_) = $this->get_raw($at);
        return [$o, $c];
    }

    /**
     * See if open (or close) tag exists at particular line.
     * --
     * @param integer $at
     *
     * @param string $tag
     *        `null` opened tags
     *        `tag`  particular opened tag,
     *        `/`    closed tags
     *        `/tag` particular close tag
     * --
     * @return boolean
     */
    function has_tag($at, $tag=null)
    {
        list($open, $_, $close) = $this->get_raw($at);
        $pool = substr($tag, 0, 1) === '/' ? $close : $open;
        $tag = trim($tag, '/');

        return $tag ? (array_search($tag, $pool) !== false) : !!count($pool);
    }

    /**
     * Erase particular tag(s).
     * --
     * @param integer $at
     * @param string  $tag   Tag name, use /tag to target close tags.
     * @param integer $count How many to erase.
     */
    function erase_tag($at, $tag, $count)
    {
        $is_close = substr($tag, 0, 1) === '/';
        $pool = !$is_close ? 0 : 2;
        $pool = &$this->lines[$at][$pool];
        $tag = trim($tag, '/');

        for ($i=count($pool)-1; $i >= 0; $i--)
        {
            if ($pool[$i] === $tag)
            {
                unset($pool[$i]);
                $count--;
            }

            if ($count === 0)
            {
                break;
            }
        }

        $pool = array_values($pool);
    }

    /**
     * Get number of open and closed tags at position.
     * --
     * @param integer $at
     *
     * @param string $tag
     *        If you don't provide a tag,
     *        it will return sum count of all tags at this line.
     * --
     * @return array `[integer $open, integer $close]`
     */
    function count_tag($at, $tag=null)
    {
        list($open, $_, $close) = $this->get_raw($at);
        return [
            $tag ? arr::count_values_of($open, $tag) : count($open),
            $tag ? arr::count_values_of($close, $tag) : count($close)
        ];
    }

    /**
     * Output lines for debuging, flat array format.
     * --
     * @return array
     */
    function debug_flat_array()
    {
        $array = [];

        foreach ($this->get_all() as $line)
        {
            $array[] = implode(',', $line[0])  . ' ' .$line[1] . ' ' . implode(',', $line[2]);
        }

        return $array;
    }
}
