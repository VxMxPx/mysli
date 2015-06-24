<?php

namespace mysli\util\markdown;

__use(__namespace__, '
    ./lines
');

/**
 * Allows you to output processed Markdown.
 */
class output
{
    const compressed = 1;
    const readable = 2;
    const flat = 3;

    /**
     * List of elements to be outputted as inline.
     * --
     * @var array
     */
    protected static $inline = [
        'p', 'img', 'i', 'u', 'strong', 'em', 'hr', 'li',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6'
    ];

    /**
     * Default options to be extended by user.
     * --
     * @var array
     * --
     * @opt string  indent_type When outputting as output::readable, what type of
     *                          indentation should be used (usually space or \t)
     * @opt string  indent_size Size of indention when outputting as output::readable
     *                          this will basically be `indenation_type` multiplier.
     */
    protected $options = [
        'indent_type' => ' ',
        'indent_size' => 4,
    ];

    /**
     * Instance of lines.
     * --
     * @var \mysli\util\markdown\lines
     */
    private $lines;

    /**
     * Instance of Output.
     * --
     * @param \mysli\util\markdown\lines $lines (see: self::$lines)
     * @param array $options (see: self::$options)
     */
    function __construct(\mysli\util\markdown\lines $lines, array $options=[])
    {
        $this->lines = $lines;
        $this->set_options($options);
    }

    /**
     * Set option(s).
     * --
     * @example $output->set_options(['indent_size' => 2]);
     * --
     * @param array $options (see: self::$options)
     */
    function set_options(array $options)
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Return output as string.
     * --
     * @param integer $f Format options are:
     *                   output::compressed all elements in one line.
     *                   output::readable nicely formated and indented elements.
     *                   output::flat each element in its own line, but no indentation.
     * --
     * @return string
     */
    function as_string($f=self::compressed)
    {
        $output = '';
        $level = 0;
        $indent = str_repeat(
            $this->options['indent_type'], $this->options['indent_size']
        );

        foreach ($this->lines->get_all() as $lineno => $elements)
        {
            list($opent, $line, $closet, $attr) = $elements;

            // TO-DO: Not if in pre-tag!
            $line = trim($line);

            if (arr::get($attr, 'skip', false))
            {
                continue;
            }

            switch ($f)
            {
                //
                // Compressed and flat format:
                //
                case self::compressed:
                case self::flat:
                    $dv = $f === self::compressed ? '' : "\n";

                    foreach ($opent as $tag)
                    {
                        $output .= $dv."<{$tag}>";
                    }

                    $output .= $dv.$line;

                    foreach ($closet as $tag)
                    {
                        $output .= $dv."</{$tag}>";
                    }
                    break;

                //
                // Print in readable format, with indentations etc,...
                //
                case self::readable:

                    // Reset tag to be sure
                    $tag = null;

                    // Put open tags...
                    foreach ($opent as $tag)
                    {
                        $output .= "\n".str_repeat($indent, $level)."<{$tag}>";
                        $level++;
                    }

                    // Check if last tag was to be inline...
                    if (($line || $tag) && !in_array($tag, self::$inline))
                    {
                        $output .=
                            "\n".
                            ($line ?
                                str_repeat($indent, $level+($tag ? 1 : 0)) :
                                ''
                            );
                    }

                    // Put element itself
                    $output .= $line;

                    // Put close tags
                    foreach ($closet as $l => $tag)
                    {
                        $level--;

                        // First close tag
                        if ($l !== 0 || !in_array($tag, self::$inline))
                        {
                            $output .= "\n".str_repeat($indent, $level);
                        }

                        $output .= "</{$tag}>";
                    }
                    break;

                default:
                    throw new exception\parser("Wrong argument: `{$f}`.");
            }
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
