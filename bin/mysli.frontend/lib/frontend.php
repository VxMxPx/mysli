<?php

namespace mysli\frontend; class frontend
{
    const __use = '
        mysli.tplp
        mysli.toolkit.{
            output
        }
    ';

    /**
     * Render particular template with content, and set output.
     * --
     * @param string $contents
     * @param array  $template
     * @param string $language
     * --
     * @return boolean
     */
    static function render($contents, array $template)
    {}

    /**
     * Output 404!
     * --
     * @return void
     */
    static function error404()
    {

    }
}
