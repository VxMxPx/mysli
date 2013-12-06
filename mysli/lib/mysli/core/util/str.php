<?php

namespace Mysli\Core\Util;

class Str
{
    // Cache, set for method normalize
    protected static $normalize_map = array(
        'Ĳ' => 'I', 'Ö' => 'O','Œ' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
        'ĳ' => 'i','ö' => 'o','œ' => 'o','ü' => 'u','ß' => 's','ſ' => 's',
        'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
        'Æ' => 'A','Ā' => 'A','Ą' => 'A','Ă' => 'A','Ç' => 'C','Ć' => 'C',
        'Č' => 'C','Ĉ' => 'C','Ċ' => 'C','Ď' => 'D','Đ' => 'D','È' => 'E',
        'É' => 'E','Ê' => 'E','Ë' => 'E','Ē' => 'E','Ę' => 'E','Ě' => 'E',
        'Ĕ' => 'E','Ė' => 'E','Ĝ' => 'G','Ğ' => 'G','Ġ' => 'G','Ģ' => 'G',
        'Ĥ' => 'H','Ħ' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
        'Ī' => 'I','Ĩ' => 'I','Ĭ' => 'I','Į' => 'I','İ' => 'I','Ĵ' => 'J',
        'Ķ' => 'K','Ľ' => 'K','Ĺ' => 'K','Ļ' => 'K','Ŀ' => 'K','Ł' => 'L',
        'Ñ' => 'N','Ń' => 'N','Ň' => 'N','Ņ' => 'N','Ŋ' => 'N','Ò' => 'O',
        'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','Ō' => 'O','Ő' => 'O',
        'Ŏ' => 'O','Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R','Ś' => 'S','Ş' => 'S',
        'Ŝ' => 'S','Ș' => 'S','Š' => 'S','Ť' => 'T','Ţ' => 'T','Ŧ' => 'T',
        'Ț' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','Ū' => 'U','Ů' => 'U',
        'Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U','Ŵ' => 'W','Ŷ' => 'Y',
        'Ÿ' => 'Y','Ý' => 'Y','Ź' => 'Z','Ż' => 'Z','Ž' => 'Z','à' => 'a',
        'á' => 'a','â' => 'a','ã' => 'a','ā' => 'a','ą' => 'a','ă' => 'a',
        'å' => 'a','ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
        'ď' => 'd','đ' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
        'ē' => 'e','ę' => 'e','ě' => 'e','ĕ' => 'e','ė' => 'e','ƒ' => 'f',
        'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g','ĥ' => 'h','ħ' => 'h',
        'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
        'ĭ' => 'i','į' => 'i','ı' => 'i','ĵ' => 'j','ķ' => 'k','ĸ' => 'k',
        'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l','ñ' => 'n',
        'ń' => 'n','ň' => 'n','ņ' => 'n','ŉ' => 'n','ŋ' => 'n','ò' => 'o',
        'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','ō' => 'o','ő' => 'o',
        'ŏ' => 'o','ŕ' => 'r','ř' => 'r','ŗ' => 'r','ś' => 's','š' => 's',
        'ť' => 't','ù' => 'u','ú' => 'u','û' => 'u','ū' => 'u','ů' => 'u',
        'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u','ŵ' => 'w','ÿ' => 'y',
        'ý' => 'y','ŷ' => 'y','ż' => 'z','ź' => 'z','ž' => 'z','Α' => 'A',
        'Ά' => 'A','Ἀ' => 'A','Ἁ' => 'A','Ἂ' => 'A','Ἃ' => 'A','Ἄ' => 'A',
        'Ἅ' => 'A','Ἆ' => 'A','Ἇ' => 'A','ᾈ' => 'A','ᾉ' => 'A','ᾊ' => 'A',
        'ᾋ' => 'A','ᾌ' => 'A','ᾍ' => 'A','ᾎ' => 'A','ᾏ' => 'A','Ᾰ' => 'A',
        'Ᾱ' => 'A','Ὰ' => 'A','ᾼ' => 'A','Β' => 'B','Γ' => 'G','Δ' => 'D',
        'Ε' => 'E','Έ' => 'E','Ἐ' => 'E','Ἑ' => 'E','Ἒ' => 'E','Ἓ' => 'E',
        'Ἔ' => 'E','Ἕ' => 'E','Ὲ' => 'E','Ζ' => 'Z','Η' => 'I','Ή' => 'I',
        'Ἠ' => 'I','Ἡ' => 'I','Ἢ' => 'I','Ἣ' => 'I','Ἤ' => 'I','Ἥ' => 'I',
        'Ἦ' => 'I','Ἧ' => 'I','ᾘ' => 'I','ᾙ' => 'I','ᾚ' => 'I','ᾛ' => 'I',
        'ᾜ' => 'I','ᾝ' => 'I','ᾞ' => 'I','ᾟ' => 'I','Ὴ' => 'I','ῌ' => 'I',
        'Θ' => 'T','Ι' => 'I','Ί' => 'I','Ϊ' => 'I','Ἰ' => 'I','Ἱ' => 'I',
        'Ἲ' => 'I','Ἳ' => 'I','Ἴ' => 'I','Ἵ' => 'I','Ἶ' => 'I','Ἷ' => 'I',
        'Ῐ' => 'I','Ῑ' => 'I','Ὶ' => 'I','Κ' => 'K','Λ' => 'L','Μ' => 'M',
        'Ν' => 'N','Ξ' => 'K','Ο' => 'O','Ό' => 'O','Ὀ' => 'O','Ὁ' => 'O',
        'Ὂ' => 'O','Ὃ' => 'O','Ὄ' => 'O','Ὅ' => 'O','Ὸ' => 'O','Π' => 'P',
        'Ρ' => 'R','Ῥ' => 'R','Σ' => 'S','Τ' => 'T','Υ' => 'Y','Ύ' => 'Y',
        'Ϋ' => 'Y','Ὑ' => 'Y','Ὓ' => 'Y','Ὕ' => 'Y','Ὗ' => 'Y','Ῠ' => 'Y',
        'Ῡ' => 'Y','Ὺ' => 'Y','Φ' => 'F','Χ' => 'X','Ψ' => 'P','Ω' => 'O',
        'Ώ' => 'O','Ὠ' => 'O','Ὡ' => 'O','Ὢ' => 'O','Ὣ' => 'O','Ὤ' => 'O',
        'Ὥ' => 'O','Ὦ' => 'O','Ὧ' => 'O','ᾨ' => 'O','ᾩ' => 'O','ᾪ' => 'O',
        'ᾫ' => 'O','ᾬ' => 'O','ᾭ' => 'O','ᾮ' => 'O','ᾯ' => 'O','Ὼ' => 'O',
        'ῼ' => 'O','α' => 'a','ά' => 'a','ἀ' => 'a','ἁ' => 'a','ἂ' => 'a',
        'ἃ' => 'a','ἄ' => 'a','ἅ' => 'a','ἆ' => 'a','ἇ' => 'a','ᾀ' => 'a',
        'ᾁ' => 'a','ᾂ' => 'a','ᾃ' => 'a','ᾄ' => 'a','ᾅ' => 'a','ᾆ' => 'a',
        'ᾇ' => 'a','ὰ' => 'a','ᾰ' => 'a','ᾱ' => 'a','ᾲ' => 'a','ᾳ' => 'a',
        'ᾴ' => 'a','ᾶ' => 'a','ᾷ' => 'a','β' => 'b','γ' => 'g','δ' => 'd',
        'ε' => 'e','έ' => 'e','ἐ' => 'e','ἑ' => 'e','ἒ' => 'e','ἓ' => 'e',
        'ἔ' => 'e','ἕ' => 'e','ὲ' => 'e','ζ' => 'z','η' => 'i','ή' => 'i',
        'ἠ' => 'i','ἡ' => 'i','ἢ' => 'i','ἣ' => 'i','ἤ' => 'i','ἥ' => 'i',
        'ἦ' => 'i','ἧ' => 'i','ᾐ' => 'i','ᾑ' => 'i','ᾒ' => 'i','ᾓ' => 'i',
        'ᾔ' => 'i','ᾕ' => 'i','ᾖ' => 'i','ᾗ' => 'i','ὴ' => 'i','ῂ' => 'i',
        'ῃ' => 'i','ῄ' => 'i','ῆ' => 'i','ῇ' => 'i','θ' => 't','ι' => 'i',
        'ί' => 'i','ϊ' => 'i','ΐ' => 'i','ἰ' => 'i','ἱ' => 'i','ἲ' => 'i',
        'ἳ' => 'i','ἴ' => 'i','ἵ' => 'i','ἶ' => 'i','ἷ' => 'i','ὶ' => 'i',
        'ῐ' => 'i','ῑ' => 'i','ῒ' => 'i','ῖ' => 'i','ῗ' => 'i','κ' => 'k',
        'λ' => 'l','μ' => 'm','ν' => 'n','ξ' => 'k','ο' => 'o','ό' => 'o',
        'ὀ' => 'o','ὁ' => 'o','ὂ' => 'o','ὃ' => 'o','ὄ' => 'o','ὅ' => 'o',
        'ὸ' => 'o','π' => 'p','ρ' => 'r','ῤ' => 'r','ῥ' => 'r','σ' => 's',
        'ς' => 's','τ' => 't','υ' => 'y','ύ' => 'y','ϋ' => 'y','ΰ' => 'y',
        'ὐ' => 'y','ὑ' => 'y','ὒ' => 'y','ὓ' => 'y','ὔ' => 'y','ὕ' => 'y',
        'ὖ' => 'y','ὗ' => 'y','ὺ' => 'y','ῠ' => 'y','ῡ' => 'y','ῢ' => 'y',
        'ῦ' => 'y','ῧ' => 'y','φ' => 'f','χ' => 'x','ψ' => 'p','ω' => 'o',
        'ώ' => 'o','ὠ' => 'o','ὡ' => 'o','ὢ' => 'o','ὣ' => 'o','ὤ' => 'o',
        'ὥ' => 'o','ὦ' => 'o','ὧ' => 'o','ᾠ' => 'o','ᾡ' => 'o','ᾢ' => 'o',
        'ᾣ' => 'o','ᾤ' => 'o','ᾥ' => 'o','ᾦ' => 'o','ᾧ' => 'o','ὼ' => 'o',
        'ῲ' => 'o','ῳ' => 'o','ῴ' => 'o','ῶ' => 'o','ῷ' => 'o','А' => 'A',
        'Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E',
        'Ж' => 'Z','З' => 'Z','И' => 'I','Й' => 'I','К' => 'K','Л' => 'L',
        'М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S',
        'Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'K','Ц' => 'T','Ч' => 'C',
        'Ш' => 'S','Щ' => 'S','Ы' => 'Y','Э' => 'E','Ю' => 'Y','Я' => 'Y',
        'а' => 'A','б' => 'B','в' => 'V','г' => 'G','д' => 'D','е' => 'E',
        'ё' => 'E','ж' => 'Z','з' => 'Z','и' => 'I','й' => 'I','к' => 'K',
        'л' => 'L','м' => 'M','н' => 'N','о' => 'O','п' => 'P','р' => 'R',
        'с' => 'S','т' => 'T','у' => 'U','ф' => 'F','х' => 'K','ц' => 'T',
        'ч' => 'C','ш' => 'S','щ' => 'S','ы' => 'Y','э' => 'E','ю' => 'Y',
        'я' => 'Y','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','ა' => 'a',
        'ბ' => 'b','გ' => 'g','დ' => 'd','ე' => 'e','ვ' => 'v','ზ' => 'z',
        'თ' => 't','ი' => 'i','კ' => 'k','ლ' => 'l','მ' => 'm','ნ' => 'n',
        'ო' => 'o','პ' => 'p','ჟ' => 'z','რ' => 'r','ს' => 's','ტ' => 't',
        'უ' => 'u','ფ' => 'p','ქ' => 'k','ღ' => 'g','ყ' => 'q','შ' => 's',
        'ჩ' => 'c','ც' => 't','ძ' => 'd','წ' => 't','ჭ' => 'c','ხ' => 'k',
        'ჯ' => 'j','ჰ' => 'h'
    );

    /**
     * Limit characters repetition.
     *   Using $char='!', $limit=1
     *   'hello world!!!!' ----> 'hello world!'
     *   Using $char=[' ', '!'], $limit=2
     *   'hello    world!!!!!!' ----> 'hello  world!!'
     * --
     * @param  string  $input
     * @param  mixed   $char  String, one char, or array of characters we want
     *                        to limit.
     * @param  integer $limit to how many characters should te limit be set
     * --
     * @return string
     */
    public static function limit_repeat($input, $char, $limit)
    {
        if (is_array($char)) {
            foreach ($char as $char_item) {
                try {
                    $input = self::limit_repeat($input, $char_item, $limit);
                }
                catch (\Mysli\Core\ValueException $e) {
                    throw $e;
                }
            }
            return $input;
        }

        if (strlen($char) < 1) {
            throw new \Mysli\Core\ValueException(
                "Expected parameter is string, long at least one character.",
                10
            );
        }

        $char_escaped = preg_quote($char);

        $limit = (int) $limit;

        if ($limit < 1) {
            throw new \Mysli\Core\ValueException(
                "Expected parameter is integer higher than one.",
                20
            );
        }

        $regex = "([{$char_escaped}]{{$limit},})";

        $input = preg_replace($regex, str_repeat($char, $limit), $input);

        return $input;
    }

    /**
     * Generate random string
     * --
     * @param   integer $length
     * @param   string  $mask
     *   a ----> qwertzuiopasdfghjklyxcvbnm
     *   A ----> QWERTZUIOPASDFGHJKLYXCVBNM
     *   1 ----> 0123456789
     *   s ----> ~#$%&()=?*<>-_:.;,+!
     * --
     * @return  string
     */
    public static function random($length, $mask = 'aA1s')
    {
        $a = 'qwertzuiopasdfghjklyxcvbnm';
        $A = 'QWERTZUIOPASDFGHJKLYXCVBNM';
        $n = '0123456789';
        $s = '~#$%&()=?*<>-_:.;,+!';

        $chars  = '';
        $chars .= ((strpos($mask, 'a') !== false) ? $a : '');
        $chars .= ((strpos($mask, 'A') !== false) ? $A : '');
        $chars .= ((strpos($mask, '1') !== false) ? $n : '');
        $chars .= ((strpos($mask, 's') !== false) ? $s : '');

        if (!chars) {
            throw new \Mysli\Core\ValueException("Invalid mask!", 1);
        }
        if ($length < 1) {
            throw new \Mysli\Core\ValueException(
                "Length must be at least one character.", 2
            );
        }

        $i = 1;
        $result = '';

        while ($i <= $length) {
            $result .= $chars{mt_rand(0,strlen($chars)-1)};
            $i++;
        }

        return $result;
    }

    /**
     * Standardize line endings to unix \n
     * --
     * @param   string  $input
     * @param   boolean $limit_lines If true it will limit empty new lines to 2.
     * --
     * @return  string
     */
    public static function to_unix_line_endings($input, $limit_lines = false)
    {
        $input = str_replace(["\r\n", "\r"], "\n", $input);
        if ($limit_lines) {
            $input = preg_replace('/\n{3,}/ism', "\n\n", $input);
        }
        return $input;
    }

    /**
     * Normalize to Clean Latin characters, for example convert ščž => scz
     *
     * @param   string  $string
     * @return  string
     */

    /**
     * Unaccent the input string. An example string like `ÀØėÿᾜὨζὅБю`
     * will be translated to `AOeyIOzoBY`. More complete than :
     *   strtr( (string)$str,
     *          "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
     *          "aaaaaaaaaaaaooooooooooooeeeeeeeecciiiiiiiiuuuuuuuuynn" );
     * --
     * @param  string  $str
     * @param  boolean $utf8 If null, function will detect input string encoding
     * --
     * @author http://www.evaisse.net/2008/php-translit-remove-accent-unaccent-21001
     * --
     * @return string
     */
    public static function normalize($str, $utf8 = true)
    {
        $str = (string) $str;

        if (is_null($utf8)) {
            if(!function_exists('mb_detect_encoding')) {
                $utf8 = (strtolower(mb_detect_encoding($str)) == 'utf-8');
            } else {
                $length = strlen($str);
                $utf8 = true;
                for ($i=0; $i < $length; $i++) {
                    $c = ord($str[$i]);
                    if ($c < 0x80) $n = 0; # 0bbbbbbb
                    elseif (($c & 0xE0) == 0xC0) $n = 1; # 110bbbbb
                    elseif (($c & 0xF0) == 0xE0) $n = 2; # 1110bbbb
                    elseif (($c & 0xF8) == 0xF0) $n = 3; # 11110bbb
                    elseif (($c & 0xFC) == 0xF8) $n = 4; # 111110bb
                    elseif (($c & 0xFE) == 0xFC) $n = 5; # 1111110b
                    else return false; # Does not match any model
                    for ($j=0; $j<$n; $j++) { # n bytes matching 10bbbbbb follow ?
                        if ((++$i == $length) ||
                            ((ord($str[$i]) & 0xC0) != 0x80)
                        ) {
                            $utf8 = false;
                            break;
                        }
                    }
                }
            }
        }

        if (!$utf8) {
            $str = utf8_encode($str);
        }

        return str_replace(
            array_keys(self::$normalize_map),
            array_values(self::$normalize_map),
            $str
        );
    }

    /**
     * Clean string data, to allow very narrow amount of specific characters.
     * --
     * @param   string  $string
     * @param   string  $mask   aA1s ---->  small a-z, up A-Z, numeric, spaces
     * @param   string  $custom custom characters ----> ,-+*!?#
     *                          Note: custom can be omitted!
     * @param   integer $limit
     * --
     * @return  string
     */
    public static function clean(
        $string,
        $mask = 'aA1s',
        $custom = false,
        $limit = 256
    ) {
        $string = (string) $string;
        if (empty($string)) { return ''; }

        $filter = '';
        $a = 'a-z';
        $A = 'A-Z';
        $n = '0-9';
        $s = preg_quote(' ');
        $c = ($custom !== false)
                ? preg_quote($custom)
                : false;

        if (!empty($mask)) {
            if (strpos($mask, 'a') !== false) { $filter .= $a; }
            if (strpos($mask, 'A') !== false) { $filter .= $A; }
            if (strpos($mask, '1') !== false) { $filter .= $n; }
            if (strpos($mask, 's') !== false) { $filter .= $s; }
            if ($c                 !== false) { $filter .= $c; }
        } else {
            throw new \Mysli\Core\ValueException('Invalid or empty mask.', 1);
        }

        if (empty($filter)) {
            throw new \Mysli\Core\ValueException('Empty filter.', 2);
        }

        // Construct regular expression filter
        $filter = '/([^' . $filter . '])/sm';

        $string = preg_replace($filter, '', $string);

        if ((int) $limit) {
            return substr($string, 0, (int) $limit);
        } else {
            return $string;
        }
    }

    /**
     * Clean string data with the help of regular expression. Removes matches.
     * --
     * @param  string  $string
     * @param  string  $regex
     * --
     * @return string
     */
    public static function clean_regex($string, $regex)
    {
        return preg_replace($regex, '', $string);
    }

    /**
     * Convert string to slug
     *   "Hello world" ----> "hello-world"
     * --
     * @param   string  $string
     * @param   array   $delimiter
     * --
     * @return  string
     */
    public static function slug($string, $delimiter = '-')
    {
        if (empty($string)) { return ''; }
        $string = mb_strtolower($string, 'UTF-8');
        $string = self::normalize($string);
        $string = self::clean($string, 'a1s', '-_', 0);
        $string = preg_replace('/( |_|-)+/', $delimiter, $string);
        $string = trim($string, $delimiter);

        return $string;
    }

    /**
     * Convert string to unique slug, you must provide list of existing slugs.
     *     "Hello world" ----> "hello-world"
     * --
     * @param   string  $string
     * @param   array   $slugs_list
     * @param   string  $delimiter
     * --
     * @return  string
     */
    public static function slug_unique($string, array $slugs_list, $delimiter = '-')
    {
        $string = self::slug($string, $delimiter);

        $num = 2;
        $base_string = $string;

        while (in_array($string, $slugs_list)) {
            $string = $base_string . $delimiter . $num;
            $num++;
        }

        return $string;
    }

    /**
     * Get desired number of words - shorten string nicely.
     * --
     * @param   string  $string
     * @param   integer $limit   To how many words should the string be limited?
     * @param   string  $ending  To be appended at the end of the string, but
     *                           only if it was shortened.
     * --
     * @return  string
     */
    public static function limit_words($string, $limit, $ending = null)
    {
        $string = (string) $string;
        $string_initial_length = mb_strlen($string);

        // Check if limit < 1
        if ($limit < 1) {
            throw new \Mysli\Core\ValueException(
                'Limit must be greater than zero!'
            );
        }

        if (strpos($string, ' ') === false) {
            return $string;
        }

        $string = implode(' ', array_slice(explode(' ', $string), 0, $limit));

        if ($ending && mb_strlen($string) !== $string_initial_length) {
            $string = $string . $ending;
        }

        return $string;
    }

    /**
     * Get desired number of characters.
     * --
     * @param   string  $string
     * @param   integer $limit  To how many characters should the string
     *                          be limited?
     * @param   string  $ending To be appended at the end of the string, but
     *                          only if it was shortened.
     * --
     * @return  string
     */
    public static function limit_length($string, $limit, $ending = null)
    {
        $string = (string) $string;
        $string_initial_length = mb_strlen($string);

        // Check if limit < 1
        if ($limit < 1) {
            throw new \Mysli\Core\ValueException(
                'Limit must be at least one character!'
            );
        }

        $string = substr($string, 0, $limit);

        if ($ending && mb_strlen($string) !== $string_initial_length) {
            $string = $string . $ending;
        }

        return $string;
    }

    /**
     * Explode and trim data.
     * --
     * @param  string  $separator  Array|String
     * @param  string  $input
     * @param  string  $trim_mask
     * @param  integer $limit
     * --
     * @return array
     */
    public static function explode_trim(
        $separator,
        $input,
        $trim_mask = null,
        $limit = false
    ) {
        // If we wanna replace by more than one item!
        if (is_array($separator)) {
            $sep_first = $separator[0];
            unset($separator[0]);

            foreach ($separator as $sep) {
                $input = str_replace($sep, $sep_first, $input);
            }

            $separator = $sep_first;
        }

        if ($limit !== false) $d = explode($separator, $input, $limit);
        else $d = explode($separator, $input);

        $f = array();

        if (is_array($d)) {
            foreach($d as $i) {
                $f[] = $trim_mask ? trim($i, $trim_mask) : trim($i);
            }
        }

        return $f;
    }

    /**
     * Explode and trim data, and get particular index.
     * --
     * @param  mixed   $separator   Array|String
     * @param  string  $input
     * @param  integer $piece_index
     * @param  string  $trim_mask
     * @param  integer $limit
     * --
     * @return string  null if not found.
     */
    public static function explode_get(
        $separator,
        $input,
        $piece_index,
        $trim_mask = null,
        $limit = false
    ) {
        $return = self::explode_trim($separator, $input, $trim_mask, $limit);
        return isset($return[$piece_index]) ? $return[$piece_index] : null;
    }

    /**
     * Explode string by separator, but ignore protected regions.
     *     id='head' class='odd new' title='it\'s a nice day!' ---->
     * Space as a separator and array() as protected:
     *     ["id='head'", "class='odd new'", "title='it\'s a nice day!'"]
     * --
     * @param  string $input
     * @param  string $separator
     * @param  array  $protected Protected regions. As single character, when
     *                           same end as start, or array, with open and
     *                           end tag.
     * --
     * @return array
     */
    public static function tokenize($input, $separator, $protected)
    {
        // Check if input is string
        if (!is_string($input) || empty($input)) return false;

        // Check if separator isn't just \ character
        if ($separator === '\\') {
            throw new \Mysli\ValueException(
                "Separator can't be backslash.", 1
            );
        }

        // Open and close of protected region
        if (is_array($protected)) {
            if (count($protected) !== 2) {
                throw new \Mysli\ValueException(
                    "Protected need to have exactly 2 elements.", 2
                );
            }

            $protected_open = $protected[0];
            $protected_close = $protected[1];
        } else {
            $protected_open = $protected;
            $protected_close = $protected;
        }

        // Define lengths + first character of open and close tag
        $open_first = substr($protected_open, 0, 1);
        $close_first = substr($protected_close, 0, 1);
        $open_length = mb_strlen($protected_open);
        $close_length = mb_strlen($protected_close);
        $input_length = mb_strlen($input);
        $separator_length = mb_strlen($separator);
        $separator_first = substr($separator, 0, 1);

        // Define protected
        $is_protected = false;
        $is_escaped = false;

        // Define empty result
        $current_token = '';
        $current_char = '';
        $result = array();

        // Now walk through string
        for ($i=0; $i < $input_length; $i++) {
            $current_char = $input[$i];

            switch ($current_char) {
                case '\\':
                    $is_escaped = true;
                    //$current_token .= '\\';
                    continue 2;

                case $close_first:
                    if (
                        substr($input, $i, $close_length) === $protected_close
                        && $is_protected
                        && !$is_escaped
                    ) {
                        $is_protected = false;
                        $current_char = $protected_close;
                        $i = $i + $close_length - 1;
                        break;
                    }
                    // Pass through

                case $open_first:
                    if (
                        substr($input, $i, $open_length) === $protected_open
                        && !$is_escaped
                    ) {
                        $is_protected = true;
                        $current_char = $protected_open;
                        $i = $i + $open_length - 1;
                    }
                    break;

                case $separator_first:
                    if (
                        substr($input, $i, $separator_length) === $separator
                        && !$is_escaped
                        && !$is_protected
                    ) {
                        $result[] = $current_token;
                        $current_token = '';
                        $i = $i + $separator_length - 1;
                        continue 2;
                    }
                    break;
            }

            // Add character to token
            $current_token .= $current_char;

            // Reset escaped state
            $is_escaped = false;
        }

        if (!empty($current_token)) {
            $result[] = $current_token;
        }

        return $result;
    }

    /**
     * Will censor particular words. If you wish you can replace word completely
     * by setting associative array in $disallowed.
     * Otherwise words will be partly or completely masked:
     * input: apple, disallowed: apple, mask: *, keep: 0
     *     ----> *****
     * input: peach, disallowed: peach, mask: *, keep: 2
     *     ----> pe***
     * input: peach, disallowed: peach, mask: *, keep: array(2, 2)
     *     ----> pe*ch
     * @param  string  $input
     * @param  mixed   $disallowed String, array or associative array
     * @param  string  $mask       Mask character
     * @param  string  $keep       Integer, or string consisting of two numbers:
     *                             1-2:
     *                             1: characters to keep on left,
     *                             2: characters to keep on right
     * --
     * @return mixed   Depends on input type.
     */
    public static function censor($input, $disallowed, $mask = '*', $keep = 2)
    {
        // We need to have input of course...
        if (!$input) return $input;

        // Disallowed must be an array
        if (!is_array($disallowed)) {
            $disallowed = array($disallowed => null);
        }
        elseif (!Arr::is_associative($disallowed)) {
            $tmp = [];
            foreach ($disallowed as $kw) {
                $tmp[$kw] = null;
            }
            $disallowed = $tmp;
        }

        // Set keep ranges
        if (is_string($keep) && strpos($keep, '-') !== false) {
            $keep = explode('-', $keep);
            $keep_left  = Arr::element(0, $keep, 0);
            $keep_right = Arr::element(1, $keep, false);
            $keep_right = $keep_right ? -($keep_right) : false;
        } else {
            $keep_left  = $keep;
            $keep_right = false;
        }

        $keep = array();
        $keep[0] = $keep_left;
        $keep[1] = $keep_right;

        foreach ($disallowed as $word => $censor) {
            $regex = '/\b('.preg_quote($word).')\b/i';
            $input = preg_replace_callback(
            $regex,
            function($match) use ($censor, $mask, $keep) {
                if (!is_null($censor)) {
                    return $censor;
                // } else if (!$keep[0]) {
                //     return str_repeat($mask, strlen($match[0]));
                } else {
                    $end = $keep[1] ? $keep[1] : mb_strlen($match[0]);
                    $mask_length = mb_strlen($match[0]);
                    $mask_length = $mask_length - $keep[0];
                    $mask_length = $mask_length + $keep[1];
                    if ($mask_length >= 0) {
                        $mask_full = str_repeat($mask, $mask_length);
                    } else {
                        return str_repeat($mask, mb_strlen($match[0]));
                    }
                    return substr_replace($match[0], $mask_full, $keep[0], $end);
                }
            },
            $input);
        }

        return $input;
    }

    /**
     * Convert to camel case
     * --
     * @param  string  $string
     * @param  boolean $uc_first  Upper case first letter also?
     * --
     * @return string
     */
    public static function to_camelcase($string, $uc_first = true)
    {

        // Convert _
        if (strpos($string, '_') !== false) {
            $string = str_replace('_', ' ', $string);
            $string = ucwords($string);
            $string = str_replace(' ', '', $string);
        }

        // Convert backslashes
        if (strpos($string, '\\') !== false) {
            $string = str_replace('\\', ' ', $string);
            $string = $uc_first ? ucwords($string) : lcfirst($string);
            $string = str_replace(' ', '\\', $string);
        }

        // Convert slashes
        if (strpos($string, '/') !== false) {
            $string = str_replace('/', ' ', $string);
            $string = $uc_first ? ucwords($string) : lcfirst($string);
            $string = str_replace(' ', '/', $string);
        }

        if (!$uc_first) {
            $string = lcfirst($string);
        } else {
            $string = ucfirst($string);
        }

        return $string;
    }

    /**
     * Convert camel case to underscores
     * --
     * @param  string  $string
     * --
     * @return string
     */
    public static function to_underscore($string)
    {
        return strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string));
    }

}
