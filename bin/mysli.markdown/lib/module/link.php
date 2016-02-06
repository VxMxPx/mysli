<?php

namespace mysli\markdown\module; class link extends std_module
{
    // Rewrite local url(s)
    protected $urls = [];
    // Missing video player message
    protected $video_message =
        "\n    Sorry, your browser doesn't support embedded videos,\n".
        "    but don't worry, you can <a href=\"{{url}}\">download it</a>\n".
        "    and watch it with your favorite video player!\n";

    function process($at)
    {
        $lines = $this->lines;

        $regbag = [
            '/(!|~)?\[([^\[]*?)\]\((.*?)(?: *"(.*?)")?\)/' => function ($match)
            {
                $title = isset($match[4]) ? " title=\"{$match[4]}\"" : '';

                list($_, $_, $txt, $url) = $match;
                $url = str_replace('"', '%22', $url);

                $url = $this->replace_local_url($url);

                if ($match[1] === '!')
                {
                    return
                        $this->seal(
                            $this->at,
                            "<img src=\"{$url}\" alt=\"{$txt}\"{$title} />");
                }
                else if ($match[1] === '~')
                {
                    if (!$txt) $txt = $this->video_message;
                    $txt = str_replace('{{url}}', $url, $txt);

                    return
                        $this->seal(
                            $this->at,
                            "<video src=\"{$url}\"{$title} controls>{$txt}</video>");
                }
                else
                {
                    return
                        $this->seal($this->at, "<a href=\"{$url}\"{$title}>").
                        "{$txt}</a>";
                }
            },
        ];

        $this->process_inline($regbag, $at);
        // Second nesting
        $this->process_inline($regbag, $at);
    }

    function set_local_url($match, $url)
    {
        $this->urls[$match] = rtrim($url, '/');
    }

    function replace_local_url($url)
    {
        foreach ($this->urls as $match => $prefix)
        {
            if (preg_match($match, $url))
            {
                return $prefix.'/'.ltrim($url, '/');
            }
        }
        return $url;
    }
}
