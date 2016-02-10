<?php

namespace mysli\markdown\module; class header extends std_module
{
    const __use = <<<fin
    mysli.toolkit.type.{ str }
fin;

    /**
     * Generated TOC
     * --
     * @var array
     */
    protected $toc   = [];

    /**
     * Internal use, current chain.
     * --
     * @var array
     */
    protected $chain = [];

    /**
     * Prevent duplicated IDs!
     * --
     * @var array
     */
    protected $taken = [];

    /**
     * Process called by parser.
     * --
     * @param integer $at
     */
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
                $this->set($at, $match[2], (int)$hl);

                $at++;
                continue;
            }

            // Setext headers
            $line = $lines->get($at+1);
            if (!$lines->is_empty($at)
                && preg_match('/^[\-|\=]+$/', $line, $match))
            {
                $hl = substr($match[0], 0, 1) === '=' ? '1' : '2';
                $title = $lines->get($at);

                // Set lines...
                $this->set($at, $title, (int)$hl);

                $lines->erase($at+1, true);
                $lines->set_attr($at+1, [
                    'no-process' => true,
                    'skip'       => true,
                ]);

                $at+2;
            }

            $at++;
        }
    }

    /**
     * Get generated TOC.
     * --
     * @return array
     */
    function get_toc()
    {
        return $this->toc;
    }

    /**
     * Set ToC item.
     * --
     * @param integer $at
     * @param string  $title
     * @param integer $level
     */
    private function set($at, $title, $level)
    {
        // Generate ID/Slug!
        $id = str::slug($title);
        $next = 2;

        // Create ToC Item
        $titem = [
            'id'    => $id,
            'fid'   => $id,
            'title' => $title,
            'level' => (int)$level,
            'items' => []
        ];

        $final = '';

        $fid = $titem['fid'];

        while (in_array($fid, $this->taken))
        {
            $fid = $titem['fid'].'-'.$next;
            $next++;
        }

        $titem['fid'] = $fid;
        $this->taken[] = $fid;

        // Add Item To The Chain
        if (!empty($this->chain))
        {
            do
            {
                unset($final);
                $final = &$this->chain[ count($this->chain)-1 ];

                // h2 // h3
                if ($final['level'] < $titem['level'])
                {
                    // $titem['fid'] = $final['fid'].'--'.$titem['fid'];
                    $final['items'][$fid] = &$titem;
                    $this->chain[] = &$titem;
                    unset($final);
                    break;
                }
                else
                {
                    array_pop($this->chain);
                }

            } while (count($this->chain));
        }

        if (isset($final))
        {
            $this->toc[$fid] = $titem;
            $this->chain[] = &$this->toc[$fid];
        }

        // Finally Set Line!
        $this->lines->set($at, $title, "h{$level}");
        $this->lines->set_attr($at, [
            'html-attributes' => [
                "h{$level}" => [ "id=\"{$fid}\"" ]
            ]
        ]);
    }
}
