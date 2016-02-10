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
        'mysli.markdown.module.code_backtick' => null,
        'mysli.markdown.module.html'          => null,
        'mysli.markdown.module.blockquote'    => null,
        'mysli.markdown.module.listf'         => null,
        'mysli.markdown.module.code_indent'   => null,
        'mysli.markdown.module.entity'        => null,
        'mysli.markdown.module.header'        => null,
        'mysli.markdown.module.rule'          => null,
        'mysli.markdown.module.container'     => null,
        'mysli.markdown.module.paragraph'     => null,
        'mysli.markdown.module.inline'        => null,
        'mysli.markdown.module.link'          => null,
        'mysli.markdown.module.url'           => null,
        'mysli.markdown.module.typography'    => null,
        'mysli.markdown.module.footnote'      => null,
        'mysli.markdown.module.abbreviation'  => null,
        'mysli.markdown.module.unseal'        => null,
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
    function __construct($markdown=null)
    {
        $this->lines = new lines();

        if ($markdown)
        {
            $this->set_markdown($markdown);
        }
    }

    /**
     * Set markdown. This will reset lines!
     * --
     * @param string $markdown
     */
    function set_markdown($markdown)
    {
        $this->markdown = explode("\n", str::to_unix_line_endings($markdown));
        $this->lines->set_lines($this->markdown);
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
     * Return all processor(s).
     * --
     * @return lines
     */
    function get_processors()
    {
        return $this->processors;
    }

    /**
     * Return particular processor by ID. This will instantiate object if not there.
     * --
     * @param string $id
     * --
     * @throws mysli\markdown\exception\parser 10 Invalid processor ID.
     * --
     * @return object
     */
    function get_processor($id)
    {
        if (!array_key_exists($id, $this->processors))
            throw new exception\parser("Invalid processor id: `{$id}`", 10);

        if (!$this->processors[$id])
        {
            $class = str_replace('.', '\\', $id);
            $this->processors[$id] = new $class($this->lines);
        }

        return $this->processors[$id];
    }

    /**
     * Run process and return output.
     * --
     * @return mysli\markdown\output
     */
    function process()
    {
        $at = 0;

        $this->lines->reset();

        // Blocks
        foreach ($this->processors as $processor => $instance)
        {
            if (!$instance)
            {
                $instance = $this->get_processor($processor);
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
