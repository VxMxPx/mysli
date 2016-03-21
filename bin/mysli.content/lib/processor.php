<?php

namespace mysli\content; class processor
{
    const __use = <<<fin
        mysli.markdown.{ markdown, parser -> markdown.parser }
fin;

    /**
     * Split blog post into multiple sections.
     * --
     * @param string   $source
     * @param callable $call
     *        function ($section, $posotion) {}
     *        If false is returned section will no be added to the return array.
     * --
     * @return array
     */
    static function slice_source($source, $call)
    {
        // Split
        $sections = preg_split('/^={3,}$/m', $source, 2);
        $processed = [];
        $position = 0;

        // Process each section
        while (count($sections))
        {
            $section = array_shift($sections);
            $r = $call($section, $position);

            if ($r !== false) $processed[] = $r;

            $position++;
        }

        return $processed;
    }

    /**
     * Process post's body
     * --
     * @param string $input
     * @param string $call
     *        Called for each individual page in post's body.
     *        function ($parser, $input) {}
     *        Markdown parser will be send as an argument.
     *        Individual markdown processors can be extracted from parser,
     *        and configured individually.
     * --
     * @return array
     */
    static function body($input, $call)
    {
        // New markdown parser
        $parser = new markdown\parser($page);

        // Callback, to modify markdown processors
        $call($parser, $input);

        // Process...
        $html = markdown::process($parser);

        // Table of Contents
        $headers = $parser->get_processor('mysli.markdown.module.header');
        $toc = $headers->as_array();

        // Footnotes
        $footnote = $parser->get_processor('mysli.markdown.module.footnote');

        // Return page basic's
        return [
            'toc'        => $toc,
            'references' => $footnote->as_array(),
            'body'       => $html
        ];
    }

    /**
     * Find all includes in an array.
     * Return modified source array!
     * Includes are files that are loaded from meta, e.g.:
     *
     *     title: My blog post
     *     sources: <<< sources.ym
     *     tags: default
     * --
     * @param callable $call
     *        For each file that is found, call, return value will replace
     *        include statement in source array.
     * --
     * @return array
     */
    static function includes(array $array, $call)
    {
        foreach ($array as &$value)
        {
            if (!is_string($value))
            {
                continue;
            }

            if (substr($value, 0, 4) === '<<< ')
            {
                $value = trim(substr($value, 4));
                $value = $call($value);
            }
        }

        return $array;
    }
}
