<?php

/**
 * # Code Module
 *
 * Match code blocks in following formats:
 *
 *     Code Block!
 *
 * ```
 * Code Block!
 * ```
 *
 * ``` php
 * Code Block!
 * ```
 */
namespace mysli\markdown\module; class code extends std_module
{
    function process($at)
    {
        $this->backticked($at);
        $this->indented($at);
    }

    /**
     * Match indented code blocks.
     * --
     * @param integer $at
     * --
     * @return void
     */
    protected function indented($at)
    {
        $lines = $this->lines;
        $opened = false;
        $last_at = false;

        while($lines->has($at))
        {
            $line = $lines->get($at);

            if (preg_match('/^(\t|\ {4})(.*?)$/', $line, $match))
            {
                // Do not open if inside of HTML tag
                if ($lines->get_attr($at, 'in-html-tag')
                    // Do not open if inside code block already!
                    || $lines->get_attr($at, 'in-code')
                    // Do not opened if previous line is not empty!
                    || (!$opened && $lines->has($at-1)
                        && !$lines->is_empty($at-1) && !$lines->has_tag($at-1)))
                {
                    $at++;
                    continue;
                }

                $line = $match[2];
                $lines->set($at, $line);
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
                    $lines->set_tag($last_at, [ false, 'pre' ]);
                    $lines->set_tag($last_at, [ false, 'code' ]);
                }
                $opened = false;
            }

            $at++;
        }

        if ($opened)
        {
            $lines->set_tag($last_at, [ false, 'pre' ]);
            $lines->set_tag($last_at, [ false, 'code' ]);
        }
    }

    /**
     * Match backticked code block.
     * --
     * @param integer $at
     * --
     * @return void
     */
    protected function backticked($at)
    {
        $lines = $this->lines;
        $opened = false;
        $count = null; // Count of open ticks

        while($lines->has($at))
        {
            $line = $lines->get($at);

            if (!$opened)
            {
                // Can't open while inside HTML tag
                if ($lines->get_attr($at, 'in-html-tag'))
                {
                    $at++;
                    continue;
                }

                if (preg_match('/^(`{3,}) ?([a-z]*)$/', $line, $match))
                {
                    $count = strlen($match[1]);

                    $lines->set_attr($at, [
                        'in-code'      => true,
                        'lock-nl'      => true,
                        'lock-trim'    => true,
                        'no-indent'    => true,
                        'convert-tags' => true,
                        'no-process'   => true,
                    ]);

                    if ($match[2])
                    {
                        $lines->set_attr($at, [
                            'html-attributes' => [
                                'code' => [ 'class="language-'.trim($match[2]).'"' ],
                            ]
                        ]);
                    }

                    $lines->set($at, '');
                    $lines->set_tag($at, ['pre', false]);
                    $lines->set_tag($at, ['code', false]);

                    $opened = true;
                }
            }
            else
            {
                if (preg_match('/^`{'.$count.'}$/', $line, $match))
                {
                    $lines->set($at, '');
                    $lines->set_tag($at, [ false, 'pre' ]);
                    $lines->set_tag($at, [ false, 'code' ]);
                    $lines->set_attr($at, [
                        'no-process' => true,
                    ]);
                    $opened = false;
                }
                else
                {
                    $lines->set_attr($at, [
                        'in-code'      => true,
                        'lock-nl'      => true,
                        'lock-trim'    => true,
                        'no-indent'    => true,
                        'convert-tags' => true,
                        'no-process'   => true,
                    ]);
                }
            }

            $at++;
        }
    }
}