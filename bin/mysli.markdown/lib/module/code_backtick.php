<?php

namespace mysli\markdown\module; class code_backtick extends std_module
{
    function process($at)
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
