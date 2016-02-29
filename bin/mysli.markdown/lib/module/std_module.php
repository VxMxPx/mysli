<?php

/**
 * Standard module to be extended.
 */
namespace mysli\markdown\module; class std_module
{
    /**
     * Lines collection.
     * --
     * @var mysli\markdown\lines
     */
    protected $lines;

    /**
     * Current position.
     * --
     * @var integer
     */
    protected $at;

    /**
     * Instance
     * --
     * @param \mysli\markdown\lines $lines
     */
    function __construct(\mysli\markdown\lines $lines)
    {
        $this->lines = $lines;
    }

    // --- Output ---

    /**
     * Overwrite: return as array.
     * --
     * @return array
     */
    function as_array()
    {
        return [];
    }

    /**
     * Overwrite: return as string.
     * --
     * @return string
     */
    function as_string()
    {
        return '';
    }

    // --- Protected ---

    /**
     * Seal particular part of the line so that it won't be further modified.
     * --
     * @param integer $at
     * @param string  $string
     * --
     * @return string
     *         Seal unique ID.
     */
    protected function seal($at, $string)
    {
        $sealed = $this->lines->get_attr($at, 'sealed');
        $sealed = is_array($sealed) ? $sealed : [];
        $id = '/S/'.(count($sealed)+1).'/F/';
        $sealed[$id] = $string;
        $this->lines->set_attr($at, 'sealed', $sealed);

        return $id;
    }

    /**
     * Unseal particular line.
     * --
     * @param integer $at
     * @param string  $line Unseal this string, with $at line's seal's.
     * --
     * @return string
     */
    protected function unseal($at, $line=null)
    {
        $sealed = $this->lines->get_attr($at, 'sealed');

        if (!$line)
            $line = $this->lines->get($at);

        if (!is_array($sealed))
        {
            return $line;
        }

        $i = 0;

        while (count($sealed) && $i < 10)
        {
            foreach ($sealed as $skey => $contents)
            {
                if (strpos($line, $skey) !== false)
                {
                    $line = str_replace($skey, $contents, $line);
                    unset($sealed[$skey]);
                }
            }

            $i++;
        }

        return $line;
    }

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
        $this->at = $at;

        while ($lines->has($this->at))
        {
            if ($lines->get_attr($this->at, 'no-process')
                || $lines->get_attr($this->at, 'html-tag-opened'))
            {
                $this->at++;
                continue;
            }

            $line = $lines->get($this->at);
            $lineprev = $line;

            foreach ($regbag as $regex => $replace)
            {
                $line = $this->replace_inline($line, $regex, $replace);
            }

            if ($lineprev !== $line)
            {
                $lines->set($this->at, $line);
                $lines->set_attr($this->at, $attr);
            }

            $this->at++;
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
        $this->at = $at;

        while (true)
        {
            if (!$lines->has($this->at) || $lines->get_attr($this->at, 'no-process')
                || $lines->get_attr($this->at, 'html-tag-opened')
                || $lines->is_empty($this->at, true))
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

                if (!$lines->has($this->at))
                {
                    return;
                }
            }
            else
            {
                $buffer[$this->at] = $lines->get($this->at);
            }

            $this->at++;
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
