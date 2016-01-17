<?php

namespace mysli\markdown\module; class abbreviation extends std_module
{
    /**
     * All defined abbreviations.
     * --
     * @var array
     */
    protected $abbreviations = [];

    function process($at)
    {
        $lines = $this->lines;
        $at_init = $at;

        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            if (preg_match('/^\s*\*\[([a-z0-9\/\._-]+)\]:(.*?)$/i', $line, $match))
            {
                $this->abbreviations[$match[1]] = trim($match[2]);
                $lines->erase($at, true);
                $lines->set_attr($at, 'no-process', true);
            }

            $at++;
        }

        // Construct filter
        if (!count($this->abbreviations))
        {
            return;
        }

        $abbrregex = [];

        foreach ($this->abbreviations as $abbr => $def)
            $abbrregex[] = preg_quote($abbr);

        $abbrregex = implode('|', $abbrregex);

        $at = $at_init;

        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            $line = preg_replace_callback(
            '/(?<![a-z0-9])('.$abbrregex.')(?![a-z0-9])/m',
            function ($match) use ($at) {

                $id = $match[1];

                if (!isset($this->abbreviations[$id]))
                {
                    return $match[0];
                }

                return $this->seal(
                    $at,
                    '<abbr title="'.$this->abbreviations[$id].'">'.
                        $match[0].
                    '</abbr>');

            }, $line);

            $lines->set($at, $line);

            $at++;
        }
    }
}
