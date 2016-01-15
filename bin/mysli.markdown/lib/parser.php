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
        mysli.toolkit.type.{ str }
    ';

    /**
     * How lines should be processed, this allows plugging costume processors.
     * --
     * @var array
     */
    protected $processors = [
        'mysli.markdown.module.html'       => null,
        'mysli.markdown.module.blockquote' => null,
        'mysli.markdown.module.listf'      => null,
        'mysli.markdown.module.code'       => null,
        'mysli.markdown.module.entity'     => null,
        'mysli.markdown.module.header'     => null,
        'mysli.markdown.module.paragraph'  => null,
        'mysli.markdown.module.inline'     => null,
        'mysli.markdown.module.link'       => null,
        'mysli.markdown.module.url'        => null,
        'mysli.markdown.module.typography' => null,
        'mysli.markdown.module.footnote'   => null,
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
     * Return processor(s).
     * --
     * @return lines
     */
    function get_processors()
    {
        return $this->processors;
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

        $at = 0;

        // Blocks
        foreach ($this->processors as $processor => &$instance)
        {
            if (!$instance)
            {
                $processor_class = str_replace('.', '\\', $processor);
                $instance = new $processor_class($this->lines);
            }

            $r = $instance->process($at);

            // Skip forward
            if (is_numeric($r)) $at = $r;

            // Break the loop
            if ($r === false) return;
        }

        return $this->lines;
    }
}
