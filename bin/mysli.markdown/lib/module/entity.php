<?php

namespace mysli\markdown\module; class entity extends std_module
{
    function process($at)
    {
        $lines = $this->lines;

        while ($lines->has($at))
        {
            // Get line
            $line = $lines->get($at);

            // Convert &, but leave &copy; ...
            $line = preg_replace('/&(?![a-z]{2,11};)/', '&amp;', $line);

            // if ($lines->get_attr($at, 'convert-tags')
            //     || (!$lines->get_attr($at, 'in-html-tag')
            //         && !$lines->get_attr($at, 'html-tag-opened')
            //         && !$lines->get_attr($at, 'html-tag-closed')))
            // {
                $line = str_replace(['<', '>'], ['&lt;', '&gt;'], $line);
            // }
            // else
            // {
            //     $line = preg_replace('/(\<(?![a-z]|\/[a-z]))/', '&lt;', $line);
            // }

            $lines->set($at, $line);

            $at++;
        }
    }
}
