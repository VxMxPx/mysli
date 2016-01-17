<?php

namespace mysli\markdown\module; class code_indent extends std_module
{
    function process($at)
    {
        $lines = $this->lines;
        $opened = false;
        $last_at = false;

        while($lines->has($at))
        {
            $line = $lines->get($at);

            if ($opened && ($lines->has_tag($at)))
            {
                $lines->set($last_at, $this->unseal($last_at));
                $lines->set_tag($last_at, [ false, 'pre' ]);
                $lines->set_tag($last_at, [ false, 'code' ]);
                $opened = false;
            }

            if (preg_match('/^(\t|\ {4})(.*?)$/', $line, $match))
            {
                // Do not open if inside code block already!
                if ($lines->get_attr($at, 'in-code')
                    // Do not opened if previous line is not empty!
                    || (!$opened && $lines->has($at-1)
                        && !$lines->is_empty($at-1) && !$lines->has_tag($at-1)))
                {
                    $at++;
                    continue;
                }

                $line = $match[2];
                $lines->set($at, $line);
                $lines->set($at, $this->unseal($at));
                $lines->set_attr($at, [
                    'in-code'      => true,
                    'lock-nl'      => true,
                    'lock-trim'    => true,
                    'no-indent'    => true,
                    'convert-tags' => true,
                    'no-process'   => true,
                ]);

                if (!$opened)
                {
                    $lines->set_tag($at, ['pre', false]);
                    $lines->set_tag($at, ['code', false]);
                }

                $opened = true;
                $last_at = $at;
            }
            else
            {
                if ($opened && trim($line))
                {
                    $lines->set($last_at, $this->unseal($last_at));
                    $lines->set_tag($last_at, [ false, 'pre' ]);
                    $lines->set_tag($last_at, [ false, 'code' ]);
                }
                else if (!$lines->is_empty($at, true))
                {
                    $opened = false;
                }
            }

            $at++;
        }

        if ($opened)
        {
            $lines->set_tag($last_at, [ false, 'pre' ]);
            $lines->set_tag($last_at, [ false, 'code' ]);
        }
    }
}
