<?php

namespace mysli\markdown\module; class link extends std_module
{
    function process($at)
    {
        $lines = $this->lines;

        $regbag = [
            '/(!)?\[(.*?)\]\((.*?)(?: *"(.*?)")?\)/' => function ($match)
            {
                $title = isset($match[4]) ? " title=\"{$match[4]}\"" : '';

                list($_, $_, $txt, $url) = $match;
                $url = str_replace('"', '%22', $url);

                if ($match[1] === '!')
                {
                    return "<img src=\"{$url}\" alt=\"{$txt}\"{$title} />";
                }
                else
                {
                    return "<a href=\"{$url}\"{$title}>{$txt}</a>";
                }
            },
        ];

        $this->process_inline($regbag, $at, [
            'html-tag-opened' => true,
            'html-tag-closed' => true
        ]);
    }
}
