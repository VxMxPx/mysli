<?php

namespace mysli\markdown\module; class paragraph extends std_module
{
    function process($at)
    {
        $lines = $this->lines;
        $last_at = false;

        while ($lines->has($at))
        {
            if ($last_at === false)
            {
                if (!$this->can_open($at))
                {
                    $at++;
                    continue;
                }

                $lines->set_tag($at, [ 'p', false ]);
                $last_at = $at;
            }
            else
            {
                if ($lines->has_tag($at) || $lines->is_empty($at, true))
                {
                    $lines->set_tag($last_at, [ false, 'p' ]);
                    $last_at = false;
                    $at--;
                }
                else if (in_array($lines->get_tag($at, -1, '/'), ['li', 'blockquote']))
                {
                    $lines->set_tag($at, [ false, 'p' ]);
                    $last_at = false;
                }
                else
                {
                    $last_at = $at;
                }
            }

            $at++;
        }

        if ($last_at !== false)
        {
            $lines->set_tag($last_at, [ false, 'p' ]);
        }
    }

    protected function can_open($at)
    {
        $l = $this->lines;

        if ($l->get_attr($at, 'no-process'))
        {
            return false;
        }

        if ($l->get_attr($at, 'is-block'))
        {
            return false;
        }

        // if (!$l->get_attr($at, 'html-tag-opened')
        //     && $l->get_attr($at, 'html-tag-closed'))
        // {
        //     return false;
        // }

        if ($l->has_tag($at)
            && !in_array($l->get_tag($at, -1), ['li', 'blockquote']))
        {
            return false;
        }

        if ($l->is_empty($at, true))
        {
            return false;
        }

        if ($l->has_tag($at, '/li') || $l->has_tag($at+1, 'li'))
        {
            return false;
        }

        return true;
    }
}
