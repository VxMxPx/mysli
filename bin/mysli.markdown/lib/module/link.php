<?php

namespace mysli\markdown\module; class link extends std_module
{
    // Rewrite local url(s) (those which does not start with [a-z]://)
    protected $local_url = '';

    function process($at)
    {
        $lines = $this->lines;

        $regbag = [
            '/(!)?\[(.*?)\]\((.*?)(?: *"(.*?)")?\)/' => function ($match)
            {
                $title = isset($match[4]) ? " title=\"{$match[4]}\"" : '';

                list($_, $_, $txt, $url) = $match;
                $url = str_replace('"', '%22', $url);

                if (!preg_match('/^[a-z]{2,5}:\/\/.*?$/', $url))
                {
                    $url = $this->get_local_url($url);
                }

                if ($match[1] === '!')
                {
                    return
                        $this->seal(
                            $this->at,
                            "<img src=\"{$url}\" alt=\"{$txt}\"{$title} />");
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
    }

    function set_local_url($url)
    {
        $this->local_url = rtrim($url, '/');
    }

    function get_local_url($uri=null)
    {
        if (!$this->local_url) return $uri;
        return $this->local_url.'/'.ltrim($uri, '/');
    }
}
