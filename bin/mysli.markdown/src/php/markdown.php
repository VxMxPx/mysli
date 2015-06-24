<?php

namespace mysli\util\markdown;

__use(__namespace__, '
    ./parser,output
');

class markdown
{
    /**
     * Process a Markdown file and return HTML.
     * --
     * @param string $markdown
     * @param array  $options (see: ./parser::$options) (see: ./output::$options)
     * --
     * @return string
     */
    static function process($markdown, array $options=[])
    {
        $parser = new parser($markdown, $options);

        return $parser->process()->as_string(
            isset($options['output']) ? $options['output'] : output::readable
        );
    }
}
