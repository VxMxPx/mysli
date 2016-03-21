<?php

namespace mysli\blog; class processor extends \mysli\content\processor
{
    const __use = <<<fin
        mysli.toolkit.type.{ arr }
        mysli.markdown.{ markdown, parser -> markdown.parser }
fin;

    /**
     * Process post's body
     * --
     * @param string $input
     * @param string $call
     *        Called for each individual page in post's body.
     *        function ($parser, $position, $body) {}
     *        Markdown parser will be send as an argument.
     *        Individual markdown processors can be extracted from parser,
     *        and configured individually.
     * --
     * @return array
     */
    static function body($source, $call)
    {
        // Split source to multiple pages
        $sources = preg_split(
            '/^={3,}$/m', $source, -1,
            PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

        // Empty pages array...
        $pages = [];

        // Go through pages and process individual page
        foreach ($sources as $position => $page)
        {
            // Actually process
            $page = static::page($page, $position, $call);

            // Only one page, call it default
            if (count($sources) === 1) $page['pid'] = '_default';

            // Set previous/next page
            if ($position > 0)
            {
                $previous = arr::last_key($pages);
                $pages[$previous]['next'] = [ $page['pid'], $page['title'] ];
                $page['previous'] = [ $pages[$previous]['pid'], $pages[$previous]['title'] ];
            }

            // Last? Single?
            $page['is_first']  = !$position;
            $page['is_last']   = $position === count($sources)-1;
            $page['is_single'] = count($sources) === 1;

            // Add page to the stack
            $pages[$page['pid']] = $page;
        }

        // Return all pages
        return $pages;
    }

    /**
     * Process one single page of a particular post.
     * Individual blog posts can have multiple pages.
     * Pages are separated with ===
     * --
     * @param string   $page
     * @param integer  $position
     * @param callable $call
     *        See static::body $call
     * --
     * @return array
     */
    static function page($page, $position, $call)
    {
        // New markdown parser
        $parser = new markdown\parser($page);

        // Callback, to modify markdown processors
        $call($parser, $position, $page);

        // Process...
        $html = markdown::process($parser);

        // Table of Contents
        $headers = $parser->get_processor('mysli.markdown.module.header');
        $toc = $headers->as_array();

        if (count($toc))
        {
            $title = reset($toc);
            $pid = $title['fid'];
            $ptitle = $title['title'];
        }
        else
        {
            $pid = "page-{$position}";
            $ptitle = "Page: {$position}";
        }

        // Footnotes
        $footnote = $parser->get_processor('mysli.markdown.module.footnote');

        // Pages
        return [
            'title'      => $ptitle,
            'pid'        => $pid,
            'toc'        => $toc,
            'references' => $footnote->as_array(),
            'index'      => $position+1,
            'next'       => null,
            'previous'   => null,
            'is_first'   => null,
            'is_last'    => null,
            'is_single'  => null,
            'body'       => $html
        ];
    }
}
