<?php

/**
 * Allows you to output processed Markdown.
 */
namespace mysli\markdown; class output
{
    const __use = '
        .{ lines, exception.parser }
        mysli.toolkit.type.{ arr }
    ';

    const compressed = 1;
    const readable = 2;
    const flat = 3;

    /**
     * List of elements to be outputted as inline, relative to the parent element.
     * --
     * @var array
     */
    protected static $inline = [ 'code' ];

    /**
     * Default options to be extended by user.
     * --
     * @var array
     * --
     * @opt string indent_type
     *      When outputting as output::readable what type of indentation
     *      should be used (usually space or \t)
     *
     * @opt string indent_size
     *      Size of indention when outputting as output::readable
     *      this will basically be `indenation_type` multiplier.
     */
    protected $options = [
        'indent_type' => ' ',
        'indent_size' => 4,
    ];

    /**
     * Instance of lines.
     * --
     * @var \mysli\markdown\lines
     */
    private $lines;

    /**
     * Instance of Output.
     * --
     * @param \mysli\util\markdown\lines $lines (see: static::$lines)
     * @param array                      $options (see: static::$options)
     */
    function __construct(\mysli\markdown\lines $lines, array $options=[])
    {
        $this->lines = $lines;
        $this->set_options($options);
    }

    /**
     * Set option(s).
     * --
     * @example $output->set_options(['indent_size' => 2]);
     * --
     * @param array $options (see: static::$options)
     */
    function set_options(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Return output as string.
     * --
     * @param integer $f
     *        Format options are:
     *        output::compressed all elements in one line.
     *        output::readable nicely formated and indented elements.
     *        output::flat each element in its own line, but no indentation.
     * --
     * @throws mysli\markdown\exception\parser 10 Negative level for tag.
     * @throws mysli\markdown\exception\parser 20 Wrong argument.
     * --
     * @return string
     */
    function as_string($f=self::compressed)
    {
        // Current output, set to empty
        $output = '';

        // Current identation level
        $level = 0;

        // One indentation unit (for example \t or ....)
        $indent = str_repeat(
            $this->options['indent_type'], $this->options['indent_size']
        );

        // Last node (either null, tag or txt)
        $last_node = null;
        // Previous tag
        $last_tag  = null;

        // Start outputing at position
        $at = 0;

        // Easy acess
        $l = $this->lines;

        while ($l->has($at))
        {
            // Actual line
            $txt = $l->get_attr($at, 'lock-trim')
                ? $l->get($at)
                : trim($l->get($at));

            // Open, close tags
            list($opent, $closet) = $l->get_tags($at);

            switch ($f)
            {
                // Compressed and flat format:
                case self::compressed:
                case self::flat:
                    $dv = $f === self::compressed && !$l->get_attr($at, 'lock-nl')
                        ? '' : "\n";
                    foreach ($opent as $tag) $output .= $dv."<{$tag}>";
                    $output .= $dv.$txt;
                    foreach ($closet as $tag) $output .= $dv."</{$tag}>";
                break;

                // Print in readable format, with indentations etc,...
                case self::readable:
                    // Open tags
                    foreach ($opent as $tn => $tag)
                    {
                        if ($last_node && !in_array($tag, static::$inline))
                        {
                            $output .= "\n".str_repeat($indent, $level);
                        }
                        $output   .= "<{$tag}>";
                        $last_node = 'tag';
                        $last_tag  = $tag;
                        $level++;
                    }

                    $last_tag  = null;

                    // TODO: This might be buggy!?
                    if (($last_node === 'txt' || $last_node === '/tag') && trim($txt))
                    {
                        $output .= "\n";

                        if (!$l->get_attr($at, 'no-indent'))
                            $output .= str_repeat($indent, $level);
                    }

                    // Put element itself
                    if (trim($txt))
                    {
                        $output   .= $txt;
                        $last_node = 'txt';
                    }

                    // Put close tags
                    foreach ($closet as $tn => $tag)
                    {
                        $level--;

                        if ($level < 0)
                        {
                            throw new exception\parser(f_error(
                                $l->debug_flat_array(), $at,
                                "Negative level for `{$tag}`.", 10
                            ));
                        }

                        if (($last_node === 'tag' || $last_node === '/tag')
                            && !in_array($last_tag, static::$inline))
                        {
                            $output .= "\n".str_repeat($indent, $level);
                        }

                        $output   .= "</{$tag}>";
                        $last_tag  = $tag;
                        $last_node = '/tag';
                    }

                    $last_tag  = null;
                break;

                // Invalid argument provided
                default:
                    throw new exception\parser("Wrong argument: `{$f}`.", 20);
            }

            $at++;
        }

        return trim($output);
    }

    /**
     * Return output as an raw array.
     * This will allow you to do post processing of HTML.
     * --
     * @return array
     */
    function as_array()
    {
        return $this->lines->get_all();
    }
}
