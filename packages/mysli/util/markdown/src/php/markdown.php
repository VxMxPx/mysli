<?php

namespace mysli\util\markdown;

__use(__namespace__, '
    ./parser
');

class markdown
{
    /**
     * Process a Markdown file and return HTML.
     * @param  string $markdown
     * @return string
     */
    static function process($markdown)
    {
        $parser = new parser($markdown);
        $parser->process();
        return $parser;
    }
}
