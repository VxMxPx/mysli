<?php

namespace Mysli\Core\Lib;

class HTML
{
    private static $headers = [];
    private static $footers = [];

    /**
     * Add Something To The Heeader
     * --
     * @param   string  $content  What we want to add to header? |
     *                            If false, header will be removed.
     * @param   mixed   $key      False for no key.
     * --
     * @return  void
     */
    public static function add_header($content, $key=false)
    {
        if ($key === false) {
            self::$headers[] = $content;
        }
        else {
            if ($content === false) {
                if (isset(self::$headers[$key])) {
                    unset(self::$headers[$key]);
                }
            }
            else {
                self::$headers[$key] = $content;
            }
        }
    }

    /**
     * Return Headers
     * --
     * @return  string
     */
    public static function get_headers()
    {
        Event::trigger('/mysli/core/lib/html::get_headers', self::$headers);

        $return = '';

        if (!empty(self::$headers)) {
            foreach(self::$headers as $header) {
                $return .= "{$header}\n";
            }
        }

        return $return;
    }

    /**
     * Add Something To The Footer
     * --
     * @param   string  $content    If false, footer will be removed.
     * @param   mixed   $key        False for no key.
     * --
     * @return  void
     */
    public static function add_footer($content, $key=false)
    {
        if ($key === false) {
            self::$footers[] = $content;
        }
        else {
            if ($content === false) {
                if (isset(self::$footers[$key])) {
                    unset(self::$footers[$key]);
                }
            }
            else {
                self::$footers[$key] = $content;
            }
        }
    }

    /**
     * Return Footers
     * --
     * @return  string
     */
    public static function get_footers()
    {
        Event::trigger('/mysli/core/lib/html::get_footers', self::$footers);

        $return = '';

        if (!empty(self::$footers)) {
            foreach(self::$footers as $footer) {
                $return .= "{$footer}\n";
            }
        }

        return $return;
    }

    /**
     * Will highlight particular text. Return full string with all highlights.
     * --
     * @param   string  $haystack
     * @param   mixed   $needle     List of words to highlight (string/array)
     * @param   string  $wrap       Tag into which we wrap the needle
     * --
     * @return  string
     */
    public static function hightlight($haystack, $needle, $wrap='<span class="highlight">%s</span>')
    {
        if (!$needle || !$haystack) {
            return $haystack;
        }

        if (is_array($needle)) {
            foreach ($needle as $ndl) {
                //if (!empty($ndl) && strlen($ndl) > 2) {
                $haystack = self::hightlight($haystack, $ndl, $wrap);
                //}
            }

            return $haystack;
        }

        $needle   = trim(str_replace('/', '', $needle));
        if (!empty($needle)) {
            $haystack = preg_replace_callback(
                '/'.preg_quote($needle).'/i',
                create_function(
                    '$Matches',
                    'return str_replace(\'%s\', $Matches[0], \''
                        .str_replace("'", "\'", $wrap).'\');'
                ),
                $haystack);
        }

        return $haystack;
    }
}