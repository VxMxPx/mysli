<?php

namespace mysli\markdown\module; class std_module
{
    function __construct(\mysli\markdown\lines $lines)
    {
        $this->lines = $lines;
    }

    // --- Output ---

    function as_array()
    {
        return [];
    }

    function as_string()
    {
        return '';
    }

    // --- Protected ---

    /**
     * Convert <space>|<tab> indent to integer.
     * --
     * @param string $indent
     * --
     * @return integer
     */
    protected function indent_to_int($indent)
    {
        $indent = str_replace("\t", '    ', $indent);
        return strlen($indent);
    }

    /**
     * Generic process inline elements.
     * --
     * @param array   $regbag
     * @param integer $at
     * @param array   $attr Set line attributes if modified.
     */
    protected function process_inline(array $regbag, $at, array $attr=[])
    {
        $lines = $this->lines;

        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process')
                || $lines->get_attr($at, 'html-tag-opened'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);
            $lineprev = $line;

            foreach ($regbag as $regex => $replace)
            {
                $line = $this->replace_inline($line, $regex, $replace);
            }

            if ($lineprev !== $line)
            {
                $lines->set($at, $line);
                $lines->set_attr($at, $attr);
            }

            $at++;
        }
    }

    /**
     * Generic process inline elements. This will check multiple lines.
     * --
     * @param array   $regbag
     * @param integer $at
     */
    protected function process_inline_multi(array $regbag, $at)
    {
        $buffer = [];
        $lines = $this->lines;

        while (true)
        {
            if (!$lines->has($at) || $lines->get_attr($at, 'no-process')
                || $lines->get_attr($at, 'html-tag-opened')
                || $lines->is_empty($at, true))
            {
                // Any buffer
                if (count($buffer))
                {
                    $buffmrg = implode("\n", $buffer);
                    $linesno  = array_keys($buffer);

                    foreach ($regbag as $find => $replace)
                    {
                        $buffmrg = $this->replace_inline($buffmrg, $find, $replace);
                    }

                    // Put modified lines
                    foreach (explode("\n", $buffmrg) as $pos => $buffline)
                    {
                        $lines->set($linesno[$pos], $buffline);
                    }

                    $buffer = [];
                }

                if (!$lines->has($at))
                {
                    return;
                }
            }
            else
            {
                $buffer[$at] = $lines->get($at);
            }

            $at++;
        }
    }

    /**
     * Flexible replacement.
     * --
     * @param string $line
     * @param mixed  $find
     * @param mixed  $replace
     * --
     * @return string
     */
    protected function replace_inline($line, $find, $replace=null)
    {
        if (is_callable($find))
        {
            return $find($line);
        }
        else if (is_array($find) || !in_array(substr($find, 0, 1), ['/', '#']))
        {
            return str_replace($find, $replace, $line);
        }
        else
        {
            if (is_callable($replace))
            {
                return preg_replace_callback($find, $replace, $line);
            }
            else
            {
                return preg_replace($find, $replace, $line);
            }
        }
    }
}
