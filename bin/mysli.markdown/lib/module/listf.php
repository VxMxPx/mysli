<?php

/**
 * Find lists.
 * --
 * @example
 * - This is a
 *     - nested
 *         - unordered list!
 *
 * 1. This is a nested
 *     1. ordered list!
 */
namespace mysli\markdown\module; class listf extends std_module
{
    /**
     * --
     * @param integer $at
     */
    function process($at)
    {
        $lines = $this->lines;
        $opened = [];
        $list_item_regex = '/^([\ |\t]*)([\*|\+|\-]|[0-9]+\.) +(.*?)$/';
        $indent_now = $indent = 0;
        $last_li = false;
        $last_empty = false;

        while ($lines->has($at))
        {
            // Skip if no process
            if ($lines->get_attr($at, 'no-process')
                || $lines->get_attr($at, 'in-html-tag'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            if (preg_match($list_item_regex, $line, $match))
            {
                $line = trim($match[3]);
                $lines->set($at, $line);
                $indent_now = $this->indent_to_int($match[1]);
                $type = in_array($match[2], ['*', '-', '+']) ? 'ul' : 'ol';
                $last_li = $at;
                $last_empty = false;

                // The list is not opened, should we open new list?
                if (empty($opened))
                {
                    $opened[] = [ $type, ($indent_now-$indent) ];
                    $indent = $indent_now;
                    // Open list
                    $lines->set_tag($at, [$type, false]);
                    $lines->set_tag($at, ['li', 'li']);
                }
                else
                {
                    // The list is not opened
                    if ($indent === $indent_now)
                    {
                        $lines->set_tag($at, ['li', 'li']);
                    }
                    else if ($indent_now > $indent)
                    {
                        $lines->erase_tag($at-1, '/li', 1);
                        $lines->set_tag($at, [ $type, false ]);
                        $lines->set_tag($at, [ 'li', 'li' ]);
                        $opened[] = [ $type, ($indent_now-$indent) ];
                        $indent = $indent_now;
                    }
                    else if ($indent_now < $indent)
                    {
                        $lines->set_tag($at, ['li', 'li']);

                        while($indent_now < $indent)
                        {
                            list($cltag, $clindent) = array_pop($opened);
                            $lines->set_tag($at-1, [false, $cltag], false);
                            $lines->set_tag($at-1, [false, 'li'], false);
                            $indent = $indent - $clindent;
                        }
                        if (empty($opened))
                            $last_li = false;

                        $indent = $indent_now;
                    }
                }
            }
            else if (!empty($opened)) // NOT preg_match
            {
                if (empty($line))
                {
                    $last_empty = true;
                }
                else if ($last_li !== false)
                {
                    $local_indent = in_array(substr($line, 0, 1), [' ', "\t"]);

                    if ($last_empty && !$local_indent)
                    {
                        $opened = array_reverse($opened);
                        $openedc = count($opened)-1;

                        foreach ($opened as $c => $close)
                        {
                            $lines->set_tag($last_li, [false, $close[0]], false);

                            if ($c < $openedc)
                            {
                                $lines->set_tag($last_li, [false, 'li'], false);
                            }
                        }

                        $opened  = [];
                        $indent_now = $indent = 0;
                        $last_li = false;
                        $last_empty = false;
                    }
                    else
                    {
                        $lines->set($at, trim($line));
                        $lines->move_tags($last_li, $at, [ false, true ]);
                        $last_li = $at;
                        $last_empty = false;
                    }
                }
                else
                {
                    $last_empty = false;
                }
            }

            $at++;
        }

        if (count($opened) && $last_li)
        {
            $opened = array_reverse($opened);
            $openedc = count($opened)-1;

            foreach ($opened as $c => $close)
            {
                $lines->set_tag($last_li, [false, $close[0]], false);

                if ($c < $openedc)
                {
                    $lines->set_tag($last_li, [false, 'li'], false);
                }
            }
        }
    }
}
