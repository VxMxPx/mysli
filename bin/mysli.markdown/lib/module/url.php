<?php

namespace mysli\markdown\module; class url extends std_module
{
    function process($at)
    {
        $regbag = [
            // URL
            '/([a-z0-9]+:\/\/.*?\.[a-z]+.*?)([.,;:]?(?>[\s"()\'\\{\\}\\[\\]]|$))/'
            => '<a href="$1">$1</a>$2',
            // Mail
            '/([a-z0-9._%+-]+@[a-z0-9][a-z0-9-]{1,61}[a-z0-9]\.[a-z]{2,})/'
            => '<a href="mailto:$1">$1</a>',
        ];

        $this->process_inline($regbag, $at, [
            'html-tag-opened' => true,
            'html-tag-closed' => true
        ]);
    }
}
