<?php

namespace mysli\util\markdown;

__use(__namespace__, '
    mysli.framework.type/str,arr
');

class parser
{
    /**
     * List of elements to be closed inline
     * @var array
     */
    protected static $inline = [
        'p', 'img', 'i', 'u', 'strong', 'em', 'hr', 'li'
    ];

    /**
     * Default options to be extended by user.
     * @var array
     */
    protected $options = [
        // Weather HTML is allowed when processing Markdown
        'allow_html' => true
    ];

    /**
     * Markdown as a string
     * @var string
     */
    protected $markdown;

    /**
     * Markdown source in lines.
     * @var array
     */
    protected $lines;

    /**
     * Processed markdown
     * @var array
     */
    protected $output;

    /**
     * List of currently opened items.
     * @var array
     */
    private $open;

    /**
     * Current list level
     * @var integer
     */
    private $list_level = false;

    /**
     * Current indentation size for list
     * @var integer
     */
    private $list_indent = 0;

    /**
     * New list item was discovered
     * @var boolean
     */
    private $list_new_item = false;

    /**
     * Weather this is next level of list.
     * @var boolean
     */
    private $list_next_level = false;


    /**
     * Construct parser.
     * @param string $markdown
     * @param array  $options
     */
    function __construct($markdown, array $options=[])
    {
        // Merge options
        $this->options = array_merge($this->options, $options);

        $this->markdown = str::to_unix_line_endings($markdown);
        $this->lines = explode("\n", $this->markdown);
    }

    /**
     * Process a Markdown file and return HTML.
     * @return string
     */
    function process()
    {
        $this->output = [];
        $this->open = [];

        foreach ($this->lines as $lineno => $line)
        {
            try
            {
                // Find blockquotes... > > >
                // -------------------------------------------------------------
                $line = $this->find_blockquote($line);

                // Find list(s)
                // -------------------------------------------------------------
                $line = $this->find_list($line);

                // * Convert & => &amp; etc...
                // -------------------------------------------------------------
                $line = $this->convert_entities($line);

                // Find header tags #, ##, ###, ...
                // -------------------------------------------------------------
                if ($this->find_header($line)) continue;

                // Find Setext style headers (e.g.: ==, --)
                // -------------------------------------------------------------
                if ($this->find_setext_header($line)) continue;

                // Trim line at this point ...
                // -------------------------------------------------------------
                $line = trim($line);

                // Find list item
                // -------------------------------------------------------------
                if ($this->apply_list_item($line))
                {
                    continue;
                }

                // Handle paragraph
                // -------------------------------------------------------------
                $line = $this->find_paragraph($line);

                // Append line finally
                // -------------------------------------------------------------
                $this->append($line);
            }
            catch (\Exception $e)
            {
                throw new exception\parser(self::f_error(
                    $this->lines, $lineno, $e->getMessage(), null
                ));
            }
        }

        // Any opened elements...
        $this->close_tags(true, true);
    }

    /**
     * Return processed Markdown as string.
     * @param  string $glue
     * @return string
     */
    function as_string($glue="\n")
    {
        return implode($glue, $this->output);
    }

    /**
     * Retrun processed Markdown as array.
     * @return array
     */
    function as_array()
    {
        return $this->output;
    }

    /**
     * This will append an element to the output list.
     * @param  string  $item
     * @param  boolean $inline append item to the previous line.
     */
    protected function append($item, $inline=false)
    {
        if (!$inline)
        {
            $this->output[] = $item;
        }
        else
        {
            $oc = count($this->output)-1;
            $this->output[$oc] = $this->output[$oc] . $item;
        }
    }

    /**
     * Check weather particular tag is opened.
     * @param  string  $tag
     * @return boolean
     */
    protected function is_open($tag)
    {
        return in_array($tag, $this->open);
    }

    /**
     * Handle closing / open of pharagraph...
     * @param  string $line
     */
    protected function find_paragraph($line)
    {
        if ($this->is_open('p'))
        {
            if (empty($line))
            {
                $this->close_paragraph();
            }
        }
        else if ($line)
        {
            if (substr($line, 0, 1) === '<')
            {
                if (substr($line, 0, 2) === '<p')
                {
                    return $line;
                }

                // Are we dealing with open html tag
                $regex = '/^<'.implode('|', self::$inline).' /';
                if (!preg_match($regex, $line))
                {
                    return $line;
                }
            }

            if (!$this->is_open('p'))
            {
                $line = "<p>{$line}";
                $this->open[] = 'p';
            }
        }

        return $line;
    }

    /**
     * Close paragraph is open...
     */
    protected function close_paragraph()
    {
        if ($this->is_open('p'))
        {
            $this->close_tags('p', 1, false);
        }
    }

    /**
     * Open particular number of tags.
     * @param  string  $tag
     * @param  integer $count
     */
    protected function open_tags($tag, $count)
    {
        for ($i=0; $i < $count; $i++)
        {
            $this->open[] = $tag;
            $this->append("<{$tag}>");
        }
    }

    /**
     * Close particular number of tags.
     * @param  mixed   $tag   | true to close tags of any type | array
     *                          with list of tags to match
     * @param  integer $count | true to close all tags of above type
     * @param  boolean $between | true to close tags of any type that are between
     */
    protected function close_tags($tag, $count, $between=true)
    {
        if ($count === true && $tag !== true)
        {
            if (is_array($tag))
            {
                // Earlier
                $earlier = count($this->open);
                $limit = $earlier;

                foreach ($tag as $stag)
                {
                    $earlier = array_search($stag, $this->open);
                    if ($earlier < $limit)
                    {
                        $limit = $earlier;
                    }
                }
            }
            else
            {
                $limit = array_search($tag, $this->open);
            }
        }
        else
        {
            $limit = 0;
        }

        for ($i=count($this->open)-1; $i >= $limit; $i--)
        {
            if ($count === 0)
            {
                break;
            }

            $element = $this->open[$i];

            if ($tag === true || $between ||
                (is_string($tag) && $tag === $element) ||
                (is_array($tag) && in_array($element, $tag)))
            {
                $this->append("</{$element}>", in_array($element, self::$inline));

                unset($this->open[$i]);

                if ($count !== true && (
                    (is_array($tag) && in_array($element, $tag)) ||
                    (is_string($tag) && $tag === $element)))
                {
                    $count--;
                }
            }
        }

        // Reset indexes
        $this->open = array_values($this->open);
    }

    /**
     * Find list items...
     * @param  string $line
     */
    protected function find_list($line)
    {
        if (preg_match('/^(\ *)([\*|\+|\-]|[0-9]+\.)([^\-|\*|\+].+)$/', $line, $match))
        {
            // If p is open, close it now...
            $this->close_paragraph();

            // Set line...
            $line = $match[3];

            // This is a new item...
            $this->list_new_item = true;
            $this->list_next_level = false;

            // Get list type (eiter ul or ol)
            $type = in_array($match[2], ['-', '*', '+']) ? 'ul' : 'ol';

            // Current line's indent
            $line_indent = strlen($match[1]);

            // We have 4 levels with lists:
            // LEVEL+, LEVEL-, LEVEL~, ALL-CLOSE

            // LEVEL+ | INIT
            if ($this->list_level === false ||
                $line_indent > ($this->list_level * $this->list_indent))
            {
                // Set new default indentation, if not set at the moment.
                // This allow us to have different indentations for different lists.
                if (!$this->list_indent)
                {
                    $this->list_indent = $line_indent;
                }

                // Increase current list level by one
                $this->list_level = $this->list_level === false ?
                    0 :
                    $this->list_level + 1;

                // Tell system that we're on a new level
                $this->list_next_level = true;

                // Open exactly one ul|ol tag
                $this->open_tags($type, 1);

                // echo "DEEP [{$this->list_level}] :: {$line}\n";
            }
            // LEVEL-
            else if ($line_indent < ($this->list_level * $this->list_indent))
            {
                // We cannot go level - if we're already at zero...
                if (!$this->list_indent || !$this->list_level)
                {
                    throw new exception\parser(
                        "Already at root level, cannot move one level down.", 1
                    );
                }

                // Calculate new level
                $new_level = ($line_indent / $this->list_indent);

                //Only proceed if new level is different than current
                if ($new_level !== $this->list_level)
                {
                    // Calculate how many tags to close
                    $this->close_tags(['ul', 'ol'], $this->list_level - $new_level);

                    // Set this level to be new
                    $this->list_level = $new_level;
                }

                // echo "OUT  [{$this->list_level}] :: {$line}\n";
            }
            // // LEVEL~
            // else
            // {
            //     // echo "SAME [{$this->list_level}] :: {$line}\n";
            // }
        }
        else
        {
            // Close list elements in a case of line break...
            if (!trim($line))
            {
                if ($this->is_open('li'))
                {
                    $this->close_tags('li', true);
                }
                if ($this->is_open('ul'))
                {
                    $this->close_tags('ul', true);
                }
                if ($this->is_open('ol'))
                {
                    $this->close_tags('ol', true);
                }

                $this->list_indent = 0;
                $this->list_level = false;
            }

            $this->list_new_item = false;
        }

        return $line;
    }

    /**
     * Apply list item line if in list item...
     * @param  string $line
     */
    protected function apply_list_item($line)
    {
        if ($this->is_open('ol') || $this->is_open('ul'))
        {
            if ($this->list_new_item)
            {
                if ($this->is_open('li') && !$this->list_next_level)
                {
                    $this->close_tags('li', 1);
                }
                $this->open_tags('li', 1);
            }
            $this->append($line, $this->list_new_item);
            return true;
        }
    }

    /**
     * Find blockquotes, > > ...
     * @param  string $line
     */
    protected function find_blockquote($line)
    {
        if (preg_match('/^((?:>\ {0,4})+>?)(.*?)$/', $line, $match))
        {
            $level = substr_count($match[1], '>');
            $line = $match[2];

            if (substr($line, 0, 2) !== '  ')
            {
                $line = trim($line);
            }

            $open_values = array_count_values($this->open);
            $open_blockquote = (isset($open_values['blockquote']) ? $open_values['blockquote'] : 0);
            $diff = $level - $open_blockquote;

            if ($diff !== 0)
            {
                $this->close_paragraph();

                if ($diff < 0)
                {
                    $this->close_tags('blockquote', -($diff));
                }
                else
                {
                    $this->open_tags('blockquote', $diff);
                }
            }
        }

        return $line;
    }

    /**
     * Find headers
     * @param  string $line
     * @return boolean
     */
    protected function find_header($line)
    {
        if (preg_match('/^(\#{1,6}) (.*?)(?: [#]+)?$/', $line, $match))
        {
            $hl = strlen($match[1]);
            $this->append("<h{$hl}>{$match[2]}</h{$hl}>");
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Find setext style headers (=======, --------)
     * @param  string $line
     * @return boolean
     */
    protected function find_setext_header($line)
    {
        $ol = count($this->output)-1;

        if ($ol < 0)
        {
            return false;
        }

        if (preg_match('/^[\-|\=]+$/', $line, $match))
        {
            $hl = substr($match[0], 0, 1) === '=' ? '1' : '2';
            // Strip <p></p>
            $title = substr($this->output[$ol], 3);
            $this->output[$ol] = "<h{$hl}>{$title}</h{$hl}>";

            array_splice($this->open, arr::find($this->open, 'p'), 1);

            return true;
        }
    }

    /**
     * Convert & => &amp; etc...
     * @param  string  $line
     * @return string
     */
    protected function convert_entities($line)
    {
        // Convert &, but leave &copy; ...
        $line = preg_replace('/&(?![a-z]{2,11};)/', '&amp;', $line);

        // Convert < >, if allow HTML
        if ($this->options['allow_html'])
        {
            $line = preg_replace('/<(?![\/a-z]{1,}(.*)>)/', '&lt;', $line);
            $line = preg_replace_callback('/(\<[a-z]{1,}.*)?(>)/', function ($match) {
                if ($match[1])
                {
                    return $match[0];
                }
                else
                {
                    return '&gt;';
                }
            }, $line);
        }
        else
        {
            $line = str_replace(['<', '>'], ['&lt;', '&gt;'], $line);
        }

        return $line;
    }


    /**
     * Format generic exception message.
     * @param  array   $lines
     * @param  integer $current
     * @param  string  $message
     * @param  string  $file
     * @return string
     */
    protected static function f_error(array $lines, $current, $message, $file=null)
    {
        return $message . "\n" . self::err_lines($lines, $current, 3) .
            ($file ? "File: `{$file}`\n" : "\n");
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
    protected static function err_lines(array $lines, $current, $padding=3)
    {
        $start    = $current - $padding;
        $end      = $current + $padding;
        $result   = '';

        for ($position = $start; $position <= $end; $position++)
        {
            if (isset($lines[$position]))
            {
                if ($position === $current)
                {
                    $result .= ">>";
                }
                else
                {
                    $result .= "  ";
                }

                $result .= ($position+1).". {$lines[$position]}\n";
            }
        }

        return $result;
    }
}
