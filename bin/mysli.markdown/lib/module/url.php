<?php

namespace mysli\markdown\module; class url extends std_module
{
    function process($at)
    {
        $regbag = [
            // URL
            '/([a-z0-9]+:\/\/.*?\.[a-z]+.*?)([.,;:]?(?>[\s"()\'\\{\\}\\[\\]]|$))/'
            => function ($m)
            {
                return
                    $this->seal($this->at, '<a href="'.$m[1].'">').
                    $m[1].'</a>'.$m[2];
            },
            // Mail
            '/([a-z0-9._%+-]+@[a-z0-9][a-z0-9-]{1,61}[a-z0-9]\.[a-z]{2,})/'
            => function ($m)
            {
                return
                    $this->seal($this->at, '<a href="mailto:'.$m[1].'">').
                    $m[1].'</a>';
            },
        ];

        $this->process_inline($regbag, $at, [
            // 'html-tag-opened' => true,
            // 'html-tag-closed' => true
        ]);
    }
}
