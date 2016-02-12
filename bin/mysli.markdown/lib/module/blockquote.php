<?php

/**
 * Process blockquotes.
 * --
 * @example
 * > This is a quote.
 * > > This is a nested quote.
 */
namespace mysli\markdown\module; class blockquote extends std_module
{
    /**
     * --
     * @param integer $at
     */
    function process($at)
    {
        $lines = $this->lines;
        $indent = 0;
        $last_at = false;
        $last_empty = false;

        while($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process')
                || $lines->get_attr($at, 'in-html-tag'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            if (preg_match('/^ {0,3}((>[ \\t]*)+)(.*?)$/', $line, $match))
            {
                list($_, $levels, $last, $line) = $match;
                $indent_now = substr_count($levels, '>');

                // Add front space if it would indicate code block...
                if (substr($last, 2, 4) === '    ' || substr($last, 2, 1) === "\t")
                {
                    $line = substr($last, 2).$line;
                }

                $lines->set($at, $line);

                if ($indent_now > $indent)
                {
                    while ($indent_now > $indent)
                    {
                        $lines->set_tag($at, ['blockquote', false]);
                        $indent++;
                    }
                }
                elseif ($indent_now < $indent)
                {
                    while ($indent_now < $indent)
                    {
                        $lines->set_tag($at, [false, 'blockquote'], false);
                        $indent--;
                    }
                }

                $lines->set_attr($at, 'in-blockquote', true);
                $indent = $indent_now;
                $last_empty = false;
                $last_at = $at;
            }
            elseif ($indent) // preg_match
            {
                if (!trim($line))
                {
                    $last_empty = true;
                }
                else
                {
                    if (!$last_empty && $last_at !== false)
                    {
                        $lines->set_attr($at, 'in-blockquote', true);
                        $lines->move_tags($last_at, $at, [false, true]);
                        $last_at = $at;
                    }
                    else
                    {
                        while ($indent)
                        {
                            $lines->set_tag($last_at, [false, 'blockquote'], false);
                            $indent--;
                        }
                        $last_at = false;
                    }
                }
            }

            $at++;
        }

        // If we had anything, close it
        while ($indent)
        {
            $lines->set_tag($last_at, [false, 'blockquote'], false);
            $indent--;
        }
    }
}
