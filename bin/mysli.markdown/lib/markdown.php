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
     * @param mixed $md String or parser instance
     * --
     * @return string
     */
    static function process($md)
    {
        // Create default parser
        if (is_string($md))
        {
            $parser = new parser($md);
        }
        else
        {
            $parser = $md;
        }

        $lines = $parser->process();

        // Create default output
        $output = new output($lines);

        return $output->as_string(output::readable);
    }
}
