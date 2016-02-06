<?php

/**
 * Resolve Containers
 *
 * @example
 * ::: class
 * I'm in container!
 * :::
 *
 * Produces:
 *
 * <div class="class">I'm in container!</div>
 */
namespace mysli\markdown\module; class container extends std_module
{
    function process($at)
    {
        $opened = null;
        $lines = $this->lines;

        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            if ($opened && preg_match('/^:::$/', $line))
            {
                // Open tag...
                list($pat, $element, $classes) = $opened;

                $element = $element ? $element : 'div';

                $lines->erase($pat, true);
                $lines->set_tag($pat, [$element, false]);
                $lines->set_attr($pat, [
                    // 'no-process' => true,
                    'html-attributes' => [
                        $element => [ 'class="'.str_replace('.', ' ', $classes).'"' ]
                    ]
                ]);

                // Close tag...
                $lines->erase($at, true);
                $lines->set_tag($at, [false, $element]);
                // $lines->set_attr($at, 'no-process', true);

                $opened = null;
            }
            else
            {
                if (preg_match('/^:::([a-z]*?)\s*([a-z0-9\.]+)$/i', $line, $match))
                {
                    $opened = [$at, $match[1], $match[2]];
                }
            }

            $at++;
        }
    }
}
