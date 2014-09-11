<?php

namespace mysli\html {
    class html {

        const ent_double     = 2; // convert double-quotes
        const ent_signle     = 4; // convert single-quotes
        const ent_substitute = 8;
        const ent_disallowed = 16;

        const ent_special = 256; // htmlspecialchars
        const ent_all     = 512; // htmlentities

        const ent_html401 = 1024;
        const ent_xml1    = 2048;
        const ent_xhtml   = 4096;
        const ent_html5   = 8192;

        /**
         * Translate internal flags to PHP's.
         * @param  integer $flags
         * @return integer
         */
        private static function translate_flags($flags) {
            $return = 0;
            if ($flags & self::ent_single && $flags & self::ent_double) {
                $return |= ENT_QUOTES;
            } elseif ($flags & self::ent_double) {
                $return |= ENT_COMPAT;
            } else {
                $return |= ENT_NOQUOTES;
            }

            if ($flags & self::ent_substitute) {
                $return |= ENT_SUBSTITUTE;
            }

            if ($flags & self::ent_disallowed) {
                $return |= ENT_DISALLOWED;
            }

            if ($flags & self::ent_html401) {
                $return |= ENT_HTML401;
            } elseif ($flags & self::ent_xml1) {
                $return |= ENT_XML1;
            } elseif ($flags & self::ent_xhtml) {
                $return |= ENT_XHTML;
            } elseif ($flags & self::ent_html5) {
                $return |= ENT_HTML5;
            }

            return $return;
        }
        /**
         * Convert all applicable characters to HTML entities.
         * Example: <strong> => &lt;strong&gt;
         * ent_special = htmlspecialchars
         * ent_all = htmlentities
         * @param  string  $string
         * @param  integer $flags defalt:
         * ent_double | ent_single | ent_substitute | ent_html401 | ent_all
         * @param  string  $encoding
         * @param  boolean $double_encode
         * @return string
         */
        static function entities_encode($string, $flags=1550, $encoding='UTF-8',
                                        $double_encode=true) {
            $tflags = self::translate_flags($flags);

            return call_user_func_array(
                (($flags & self::ent_special)
                    ? 'htmlspecialchars' : 'htmlentities'),
                [$string, $tflags, $encoding, $double_encode]);
        }
        /**
         * Convert all HTML entities to their applicable characters.
         * Example: &lt;strong&gt; => <strong>
         * ent_special = htmlspecialchars_decode
         * ent_all = html_entity_decode
         * @param  string  $string
         * @param  integer $flags defalt:
         * ent_double | ent_single | ent_html401 | ent_all
         * @param  string  $encoding
         * @return string
         */
        static function entities_decode($string, $flags=1542,
                                        $encoding='UTF-8') {
            $tflags = self::translate_flags($flags);

            return call_user_func_array(
                (($flags & self::ent_special)
                    ? 'htmlspecialchars_decode' : 'html_entity_decode'),
                [$string, $tflags, $encoding]);
        }
        /**
         * Inserts HTML line breaks before all newlines in a string.
         * Example: Hello\nWorld => Hello<br/>\nWorld
         * @param  string $string
         * @return string
         */
        static function nl_to_br($string) {
            return nl2br($string, true);
        }
        /**
         * Strip HTML and PHP tags from a string.
         * @param  string $string
         * @param  string $allowed e.g. <p><a><div>
         * @return string
         */
        static function strip_tags($string, $allowed=null) {
            return $allowed
                ? strip_tags($string, $allowed)
                : strip_tags($string);
        }
    }
}
