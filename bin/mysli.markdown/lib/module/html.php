<?php

namespace mysli\markdown\module; class html extends std_module
{
    /**
     * Which elements can be contained inside other tags.
     * Lines with tags not on this list, will be skipped and not processed in
     * any way, until closing tag is found.
     * --
     * @var array
     */
    protected $contained = [
        'a', 'abbr', 'address', 'audio', 'b', 'br', 'button', 'caption', 'cite',
        'code', 'del', 'dfn', 'em', 'figcaption', 'figure', 'h1', 'h2', 'h3',
        'h4', 'h5', 'h6', 'hr', 'ins', 'img', 'sub', 'sup', 'small', 'time', 'video'
    ];

    function process($at)
    {
        $lines = $this->lines;
        $opened = [];
        $in_at = $at;

        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);
            $here = [
                'opened' => [],
            ];

            // Find (tags on this line)
            $tags = [];
            preg_match_all(
                '#\<(\/?)([a-z]+)[ |\>|\/]{1}#', $line, $tags, PREG_SET_ORDER);

            foreach ($tags as $tag)
            {
                list($_, $closed, $tag) = $tag;
                $closed = !!$closed;

                if ($closed)
                {
                    if (in_array($tag, $here['opened']))
                        unset($here['opened'][array_search($tag, $here['opened'])]);

                    if (in_array($tag, $opened))
                        unset($opened[array_search($tag, $opened)]);

                    if (empty($here['opened']))
                        $lines->set_attr($at, 'html-tag-closed', true);
                }
                else
                {
                    $opened[] = $tag;
                    $here['opened'][] = $tag;

                    $lines->set_attr($at, 'html-tag-opened', true);

                    if (!in_array($tag, $this->contained))
                    {
                        $lines->set_attr($at, [
                            'no-process'         => true,
                            'no-process-by-open' => true,
                            // 'lock-trim'          => true,
                            // 'no-indent'          => true,
                            // 'lock-nl'            => true,
                        ]);
                    }
                }
            }

            if (count($opened))
            {
                $lines->set_attr($at, 'in-html-tag', true);

                foreach ($opened as $tag)
                {
                    if (!in_array($tag, $this->contained))
                    {
                        // Need for later cleanup... :>
                        $lines->set_attr(
                            $at,
                            'html-opened-list',
                            $lines->get_attr($at, 'html-opened').'::'.$tag
                        );

                        $lines->set_attr($at, [
                            'no-process' => true,
                        ]);
                    }
                }
            }

            $at++;
        }

        // Cleanup, cannot find some opened tags...?
        if (count($opened))
        {
            foreach ($opened as $tag)
            {
                $this->cleanup($in_at, $tag);
            }
        }
    }

    protected function cleanup($at, $tag)
    {
        $lines = $this->lines;

        while ($lines->has($at))
        {
            $openedlist = $lines->get_attr($at, 'html-opened-list');

            if ($openedlist && !$lines->get_attr($at, 'no-process-by-open'))
            {
                $p = false;

                if (false !== ($p = strpos($openedlist, "::{$tag}")))
                {
                    $openedlist =
                        substr($openedlist, 0, $p).
                        substr($openedlist, $p+strlen($tag)+2);

                    if (!trim($openedlist))
                    {
                        $lines->set_attr($at, [
                            'html-opened-list'   => false,
                            'no-process'         => false,
                            'in-html-tag'        => false,
                            // 'lock-trim'          => false,
                            // 'no-indent'          => false,
                            // 'lock-nl'            => false
                        ]);
                    }
                    else
                    {
                        $lines->set_attr($at, 'html-opened-list', $openedlist);
                    }
                }
            }

            $at++;
        }
    }
}
