<?php

namespace mysli\markdown\module; class header extends std_module
{
    function process($at)
    {
        $lines = $this->lines;

        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            // Regular style headers...
            if (preg_match('/^(\#{1,6}) (.*?)(?: [#]+)?$/', $line, $match))
            {
                $hl = strlen($match[1]);

                // Set lines...
                $lines->set($at, $match[2], "h{$hl}");

                $at++;
                continue;
            }

            // Setext headers
            $line = $lines->get($at+1);
            if (preg_match('/^[\-|\=]+$/', $line, $match))
            {
                $hl = substr($match[0], 0, 1) === '=' ? '1' : '2';
                $title = $lines->get($at);

                // Set lines...
                $lines->set($at, $title, "h{$hl}");
                $lines->erase($at+1, true, true);
                $lines->set_attr($at+1, 'skip', true);

                $at+2;
            }

            $at++;
        }
    }
}
