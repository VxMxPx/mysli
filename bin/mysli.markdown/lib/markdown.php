<?php

namespace mysli\markdown; class markdown
{
    const __use = '
        .{ parser, output }
    ';

    /**
     * Process a Markdown file and return HTML.
     * If you need more control, construct your own parser (and output).
     * --
     * @param string $markdown
     * --
     * @return string
     */
    static function process($markdown)
    {
        // Create default parser
        $parser = new parser($markdown);
        $lines = $parser->process();

        // Create default output
        $output = new output($lines);
        return $output->as_string(output::readable);
    }
}
