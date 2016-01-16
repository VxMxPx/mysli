<?php

/**
 * Process footnotes.
 *
 * @example
 * Hello world! [^first].
 * Hello world ^[Woo, inline footnote!]
 * Reference back to the first one! [^first]
 * [^first]: Footnote **can have markup**!
 */
namespace mysli\markdown\module; class footnote extends std_module
{
    /**
     * Collection of footnotes.
     * --
     * @var array
     */
    protected $footnotes = [];

    /**
     * List of inline footnotes. Used to establish a proper order.
     * --
     * @var array
     */
    protected $footnotes_inline = [];

    /**
     * Process footnotes.
     * --
     * @param  integer $at
     * --
     * @return void
     */
    function process($at)
    {
        $lines = $this->lines;
        $in_fnote = false;
        $fid = null;
        $at_init = $at;

        // Grab footnote definitions
        while ($lines->has($at))
        {
            if ($lines->get_attr($at, 'no-process'))
            {
                $at++;
                continue;
            }

            $line = $lines->get($at);

            // In footnote definition
            if ($in_fnote)
            {
                list($otag, $ctag) = $lines->get_tags($at);

                $this->footnotes[$fid]['body'] .= ' '.trim($lines->get($at));
                $lines->erase($at, true);

                if (in_array('p', $ctag))
                {
                    $in_fnote = false;
                    $fid = null;
                }
            }
            // Footnote definition start
            else if (preg_match('/(?>[ \t]*|^)\[\^([a-z0-9_-]+)\]:(.*?)$/i', $line, $match))
            {
                $fid = $match[1];
                $this->footnotes[$fid]['body'] = trim($match[2]);
                $lines->erase($at, true);
                $in_fnote = true;
            }

            $at++;
        }

        $regbag = [
            '/\[\^([a-z0-9_-]+)\]/i' => function ($match)
            {
                $id = $match[1];
                return $this->process_inline_ref($id);
            },
            '/\^\[(.*?)\]/' => function ($match)
            {
                $fid = 'auto-fn-'.count($this->footnotes_inline);
                $this->footnotes[$fid]['body'] = $match[1];
                return $this->process_inline_ref($fid);
            }
        ];

        $this->process_inline($regbag, $at_init, [
            'html-tag-opened' => true,
            'html-tag-closed' => true
        ]);
    }

    function as_array()
    {
        return $this->footnotes;
    }

    protected function process_inline_ref($id)
    {
        if (!isset($this->footnotes_inline[$id]))
        {
            $this->footnotes_inline[$id] = [
                'position' => count($this->footnotes_inline),
                'count'    => 0,
            ];
        }
        else
        {
            $this->footnotes_inline[$id]['count']++;
        }

        $fcount = $this->footnotes_inline[$id]['count'];
        $fposition = $this->footnotes_inline[$id]['position']+1;

        $fnref = ($fcount > 0)
            ? "fnref{$fposition}:{$fcount}"
            : "fnref{$fposition}";

        $fn = "fn{$fposition}";

        $this->footnotes[$id]['back'][] = $fnref;

        return "<sup class=\"footnote-ref\">".
            "<a href=\"#{$fn}\" id=\"{$fnref}\">[{$fposition}]</a>".
            "</sup>";
    }
}
