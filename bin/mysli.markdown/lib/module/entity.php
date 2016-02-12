<?php

/**
 * Convert entities.
 * Convert & to &amp; (but leave &[a-z]; like for example &copy, ...)
 */
namespace mysli\markdown\module; class entity extends std_module
{
    /**
     * --
     * @param integer $at
     */
    function process($at)
    {
        $lines = $this->lines;

        while ($lines->has($at))
        {
            // Get line
            $line = $lines->get($at);

            // Convert &, but leave &copy; ...
            $line = preg_replace('/&(?![a-z]{2,11};)/', '&amp;', $line);
            $line = str_replace(['<', '>'], ['&lt;', '&gt;'], $line);

            $lines->set($at, $line);

            $at++;
        }
    }
}
