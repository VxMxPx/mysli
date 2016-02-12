<?php

/**
 * Find actual HTML tags in string, i.e. user defined tags!
 */
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
        'code', 'del', 'dfn', 'em', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'hr', 'ins', 'img', 'sub', 'sup', 'small', 'time', 'video'
    ];

    /**
     * --
     * @param integer $at
     */
    function process($at)
    {
        $lines = $this->lines;
        $marked = [];

        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            $line = preg_replace_callback(
            '#\<(\/?)([a-zA-Z]+)(.*?)(\/?)\>#sm',
            function ($match) use ($at, &$marked)
            {
                list($full, $close, $tag, $args, $self_close) = $match;

                $close = !empty($close);
                $self_close = !empty($self_close);

                if (!$self_close)
                {
                    if (!$close)
                    {
                        $marked[] = [ $tag, $at ];
                    }
                    else
                    {
                        $marked = $this->set_closed( $tag, $at, $marked );
                    }
                }

                return $this->seal($at, $full);

            }, $line);

            $lines->set($at, $line);

            $at++;
        }

        $this->mark_lines($marked);
    }

    /**
     * Set close HTML tag (set marker position).
     * --
     * @param string  $tag
     * @param integer $at
     * @param array   $marked
     * --
     * @return array
     */
    protected function set_closed($tag, $at, array $marked)
    {
        for ($i = count($marked)-1; $i>=0; $i--)
        {
            if ($marked[$i][0] === $tag && count($marked[$i] === 2))
            {
                $marked[$i][] = $at;
                break;
            }
        }

        return $marked;
    }

    /**
     * Mark lines, actually set open/close tags.
     * --
     * @param array $marked
     */
    protected function mark_lines(array $marked)
    {
        foreach ($marked as $marker)
        {
            if (count($marker) !== 3)
            {
                continue;
            }


            for ($i = $marker[1]; $i<=$marker[2]; $i++)
            {
                $is_block = $this->lines->get_attr($i, 'is-block');
                $is_block = $is_block
                    ? $is_block
                    : !in_array($marker[0], $this->contained);

                $this->lines->set_attr($i, [
                    'in-html-tag' => true,
                    'is-block'    => $is_block,
                ]);
            }
        }
    }
}
