<?php

namespace mysli\toolkit\type; class str
{
    const __use = '
        .{
            log,
            type.validate,
            type.int,
            exception.* -> toolkit.exception.*
        }
    ';

    /**
     * Set/Get internal character encoding.
     * --
     * @param string $encoding
     * --
     * @return string
     */
    static function encoding($encoding=null)
    {
        if ($encoding)
        {
            log::info("Set internal encoding to: `{$encoding}`.", __CLASS__);

            if (!mb_internal_encoding($encoding))
            {
                throw new toolkit\exception\argument(
                    "Invalid encoding: `{$encoding}`.", 1
                );
            }
        }

        return mb_internal_encoding();
    }

    /**
     * Split and trim data.
     * --
     * @param string  $string
     * @param mixed   $separator array or string
     * @param integer $limit
     * @param string  $mask
     * --
     * @return array
     */
    static function split_trim($string, $separator, $limit=null, $mask=null)
    {
        validate::need_str($string);

        if (is_array($separator))
        {
            $first = array_shift($separator);

            foreach ($separator as $s)
            {
                $string = str_replace($s, $first, $string);
            }

            $separator = $first;
        }

        if ($limit !== null)
        {
            validate::need_int($limit);
            $segments = explode($separator, $string, $limit);
        }
        else
        {
            $segments = explode($separator, $string);
        }

        $return = [];

        foreach($segments as $segment)
        {
            if ($mask !== null)
            {
                $return[] = trim($segment, $mask);
            }
            else
            {
                $return[] = trim($segment);
            }
        }

        return $return;
    }

    /**
     * Get string length (unicode).
     * --
     * @param string $string
     * @param string $encoding
     * --
     * @return integer
     */
    static function length($string, $encoding=null)
    {
        $r = mb_strlen($string, mb_internal_encoding());

        if ($r === false)
        {
            throw new toolkit\exception\argument(
                "Cannot get string length. Probably invalid encoding ".
                "was set: `{$encoding}`.", 2
            );
        }
        else
        {
            return $r;
        }
    }

    /**
     * Limit characters repetition.
     * --
     * @example
     *     $input = hello world!!!!
     *     $char  = !
     *     $limit = 1
     *     return   hello world!
     *
     *     $input = hello    world!!!!!!
     *     $char  = [' ', '!']
     *     $limit = 2
     *     return   hello  world!!
     * --
     * @param string  $input
     *
     * @param mixed   $char
     *        String, one character or an array of characters.
     *
     * @param integer $limit
     *        To how many characters should the limit be set.
     * --
     * @return string
     */
    static function limit_repeat($input, $char, $limit=1)
    {
        if (is_array($char))
        {
            foreach ($char as $one_char)
            {
                $input = self::limit_repeat($input, $one_char, $limit);
            }

            return $input;
        }

        validate::need_str($char, 1);
        validate::need_str($input, 2);
        validate::need_int($limit, 1);

        $char_escaped = preg_quote($char, '/');
        $regex = "([{$char_escaped}]{{$limit},})";
        $input = preg_replace($regex, str_repeat($char, $limit), $input);

        return $input;
    }

    /**
     * Generate a random string.
     * --
     * @param integer $length
     * @param string $mask
     *        Options:
     *        - a => qwertzuiopasdfghjklyxcvbnm
     *        - A => QWERTZUIOPASDFGHJKLYXCVBNM
     *        - 1 => 0123456789
     *        - s => ~#$%&()=?*<>-_:.;,+!
     * --
     * @return string
     */
    static function random($length, $mask='aA1s')
    {
        validate::need_int($length, 1);
        validate::need_str($mask);

        $a = 'qwertzuiopasdfghjklyxcvbnm';
        $A = 'QWERTZUIOPASDFGHJKLYXCVBNM';
        $n = '0123456789';
        $s = '~#$%&()=?*<>-_:.;,+!';

        $chars  = '';
        $chars .= ((strpos($mask, 'a') !== false) ? $a : '');
        $chars .= ((strpos($mask, 'A') !== false) ? $A : '');
        $chars .= ((strpos($mask, '1') !== false) ? $n : '');
        $chars .= ((strpos($mask, 's') !== false) ? $s : '');

        $i = 1;
        $result = '';

        while ($i <= $length)
        {
            $result .= $chars{int::random(0, strlen($chars)-1)};
            $i++;
        }

        return $result;
    }

    /**
     * Standardize line endings to unix \n
     * --
     * @param string  $input
     * @param boolean $limit_lines
     *        True to limit empty new lines to 2.
     * --
     * @return string
     */
    static function to_unix_line_endings($input, $limit_lines=false)
    {
        $input = str_replace(["\r\n", "\r"], "\n", $input);

        if ($limit_lines)
        {
            $input = preg_replace('/\n{3,}/ism', "\n\n", $input);
        }

        return $input;
    }

    /**
     * Unaccent the input string.
     * --
     * @example
     *     `ÀØėÿᾜὨζὅБю`
     * Will be translated to
     *     `AOeyIOzoBY`
     * --
     * @param string  $str
     * @param boolean $utf8
     *        If null function will detect input string encoding.
     * --
     * @author http://www.evaisse.net/2008/php-translit-remove-accent-unaccent-21001
     * --
     * @return string
     */
    static function normalize($str, $utf8=true)
    {
        $str = (string) $str;

        if (is_null($utf8))
        {
            $utf8 = (strtolower(mb_detect_encoding($str)) == 'utf-8');
        }

        if (!$utf8)
        {
            $str = utf8_encode($str);
        }

        return str_replace(
            array_keys(self::$normalize_map),
            array_values(self::$normalize_map),
            $str
        );
    }

    /**
     * Slice string (unicode).
     * --
     * @param string  $string
     * @param integer $start
     * @param integer $length
     * --
     * @return string
     */
    static function slice($string, $start, $length=null)
    {
        validate::need_str($string);
        validate::need_int($start, null, null, 1);

        if ($length !== null)
        {
            validate::need_int($length, null, null, 2);
            return mb_substr($string, $start, $length);
        }
        else
        {
            return mb_substr($string, $start);
        }
    }

    /**
     * Clean string data, to allow very narrow amount of specific characters.
     * --
     * @param string $string
     * @param string $mask   aA1s = small a-z, up A-Z, numeric, spaces.
     * @param string $custom Any custom characters (like ,-+*!?#).
     * --
     * @return string
     */
    static function clean($string, $mask='aA1s', $custom=null)
    {
        validate::need_str($string, 1);
        validate::need_str($mask, 2);

        if ($custom)
        {
            validate::need_str_or_int($custom);
        }

        if (empty($string))
        {
            return '';
        }

        $filter = '';
        $a = 'a-z';
        $A = 'A-Z';
        $n = '0-9';
        $s = preg_quote(' ', '/');

        if ($mask)
        {
            if (strpos($mask, 'a') !== false) { $filter .= $a; }
            if (strpos($mask, 'A') !== false) { $filter .= $A; }
            if (strpos($mask, '1') !== false) { $filter .= $n; }
            if (strpos($mask, 's') !== false) { $filter .= $s; }
            if ($custom            !== null)  { $filter .= preg_quote($custom, '/'); }
        }

        if (!$filter)
        {
            throw new toolkit\exception\argument("Invalid \$mask parameter.", 2);
        }

        $filter = '/([^' . $filter . '])/sm';
        $string = preg_replace($filter, '', $string);

        return $string;
    }

    /**
     * Clean string data with the help of regular expression.
     * Remove matches, use ^ to invert, for example: /[^a-z]/i
     * --
     * @param string $string
     * @param string $regex
     * --
     * @return string
     */
    static function clean_regex($string, $regex)
    {
        validate::need_str($string, 1);
        validate::need_str($regex, 2);
        return preg_replace($regex, '', $string);
    }

    /**
     * Convert string to a slug "Hello world" => "hello-world".
     * --
     * @param string $string
     * @param array  $delimiter
     * --
     * @return string
     */
    static function slug($string, $delimiter='-')
    {
        validate::need_str($string);
        validate::need_str($delimiter, 1);

        if (empty($string))
        {
            return '';
        }

        $string = self::to_lower($string);
        $string = self::normalize($string);
        $string = self::clean($string, 'a1s', '-_');
        $string = preg_replace('/( |_|-)+/', $delimiter, $string);
        $string = trim($string, $delimiter);

        return $string;
    }

    /**
     * Convert string to unique slug.
     * You must provide list of existing slugs.
     * 00
     * @param string $string
     * @param array  $slugs
     * @param string $delimiter
     * --
     * @return string
     */
    static function slug_unique($string, array $slugs, $delimiter='-')
    {
        $string = self::slug($string, $delimiter);
        $num = 2;
        $base_string = $string;

        while (in_array($string, $slugs))
        {
            $string = $base_string . $delimiter . $num;
            $num++;
        }

        return $string;
    }

    /**
     * Get desired number of words, shorten string nicely.
     * --
     * @param string  $string
     *
     * @param integer $limit
     *        How many words are allowed.
     *
     * @param string  $ending
     *        Appended at the end of the string, but only if it was shortened.
     * --
     * @return string
     */
    static function limit_words($string, $limit, $ending=null)
    {
        validate::need_int($limit, 1);
        $string = (string) $string;
        $string_initial_length = self::length($string);

        if (strpos($string, ' ') === false)
        {
            return $string;
        }

        $string = implode(' ', array_slice(explode(' ', $string), 0, $limit));

        if ($ending && self::length($string) !== $string_initial_length)
        {
            $string = $string . $ending;
        }

        return $string;
    }

    /**
     * Get desired number of characters.
     * --
     * @param string $string
     *
     * @param integer $limit
     *        How many characters should the string be limited to?
     *
     * @param string $ending
     *        To be appended at the end of the string,
     *        but only if it was shortened.
     * --
     * @return string
     */
    static function limit_length($string, $limit, $ending=null)
    {
        validate::need_int($limit, 1);
        $string = (string) $string;
        $string_initial_length = self::length($string);
        $string = self::slice($string, 0, $limit);

        if ($ending && self::length($string) !== $string_initial_length)
        {
            $string = $string . $ending;
        }

        return $string;
    }

    /**
     * Explode, trim and get a particular index.
     * --
     * @param string  $string
     * @param mixed   $separator array | string
     * @param integer $index     Index to get.
     * @param mixed   $default   Default value if index not found.
     * @param string  $mask      Trim mask.
     * @param integer $limit     Split limit.
     * --
     * @return string null if not found.
     */
    static function split_get(
        $string, $separator, $index, $default=null, $mask=null, $limit=null)
    {
        $return = self::split_trim($string, $separator, $limit, $mask);
        return arr::key_in($return, $index) ? $return[$index] : $default;
    }

    /**
     * Explode string by separator, but ignore protected regions.
     * --
     * @example
     *     id='head' class='odd new' title='it\'s a nice day!' =>
     * Space as a separator and array() as protected:
     *     ["id='head'", "class='odd new'", "title='it\'s a nice day!'"]
     * --
     * @param string $input
     * @param string $separator
     * @param array  $protected
     *        Protected regions. As single character when same end as start,
     *        or array, with open and end tag.
     * --
     * @return array
     */
    static function tokenize($input, $separator, $protected)
    {
        validate::need_str($input);

        if ($separator === '\\')
        {
            throw new toolkit\exception\argument(
                "Separator can't be backslash.", 1
            );
        }

        // Open and close of protected region
        if (is_array($protected))
        {
            if (count($protected) !== 2)
            {
                throw new toolkit\exception\argument(
                    "\$protected need to have exactly 2 elements.", 2
                );
            }
            $popen = $protected[0];
            $pclose = $protected[1];
        }
        else
        {
            $popen = $protected;
            $pclose = $protected;
        }

        // Define lengths + first character of open and close tag
        $open_first   = self::slice($popen, 0, 1);
        $close_first  = self::slice($pclose, 0, 1);
        $open_length  = self::length($popen);
        $close_length = self::length($pclose);
        $input_length = self::length($input);
        $sep_length   = self::length($separator);
        $sep_first    = self::slice($separator, 0, 1);

        // Define protected
        $is_protected = false;
        $is_escaped = false;

        // Define empty result
        $current_token = '';
        $current_char = '';
        $result = array();

        // Walk through string
        for ($i=0; $i < $input_length; $i++)
        {
            $current_char = $input[$i];
            switch ($current_char)
            {
                case '\\':
                    $is_escaped = true;
                    //$current_token .= '\\';
                    continue 2;

                case $close_first:
                    if (self::slice($input, $i, $close_length) === $pclose &&
                        $is_protected && !$is_escaped)
                    {
                        $is_protected = false;
                        $current_char = $pclose;
                        $i = $i + $close_length - 1;
                        break;
                    }
                    // Pass through

                case $open_first:
                    if (self::slice($input, $i, $open_length) === $popen &&
                        !$is_escaped)
                    {
                        $is_protected = true;
                        $current_char = $popen;
                        $i = $i + $open_length - 1;
                    }
                    break;

                case $sep_first:
                    if (self::slice($input, $i, $sep_length) === $separator &&
                        !$is_escaped && !$is_protected)
                    {
                        $result[] = $current_token;
                        $current_token = '';
                        $i = $i + $sep_length - 1;
                        continue 2;
                    }
                    break;
            }
            // Add character to token
            $current_token .= $current_char;
            // Reset escaped state
            $is_escaped = false;
        }

        if (!empty($current_token))
        {
            $result[] = $current_token;
        }

        return $result;
    }

    /**
     * Find position of first occurrence of string in a string.
     * --
     * @param string  $string
     * @param string  $find
     * @param integer $offset
     * @param string  $encoding
     * --
     * @return integer false if not found
     */
    static function find($string, $find, $offset=0, $encoding=null)
    {
        validate::need_str($string, 1);
        validate::need_str($find, 2);
        validate::need_int($offset, 0);

        if ($encoding === null)
        {
            $encoding = self::encoding();
        }

        return mb_strpos($string, $find, $offset, $encoding);
    }

    /**
     * Convert to camel case.
     * --
     * @param  string  $string
     * @param  boolean $uc_first Upper case first letter also?
     * --
     * @return string
     */
    static function to_camelcase($string, $uc_first=true)
    {
        validate::need_str($string);

        // Convert _
        if (self::find($string, '_') !== false)
        {
            $string = str_replace('_', ' ', $string);
            $string = ucwords($string);
            $string = str_replace(' ', '', $string);
        }

        // Convert backslashes
        if (self::find($string, '\\') !== false)
        {
            $string = str_replace('\\', ' ', $string);
            $string = $uc_first ? ucwords($string) : lcfirst($string);
            $string = str_replace(' ', '\\', $string);
        }

        // Convert slashes
        if (self::find($string, '/') !== false)
        {
            $string = str_replace('/', ' ', $string);
            $string = $uc_first ? ucwords($string) : lcfirst($string);
            $string = str_replace(' ', '/', $string);
        }

        if (!$uc_first)
        {
            $string = lcfirst($string);
        }
        else
        {
            $string = ucfirst($string);
        }

        return $string;
    }

    /**
     * Convert camel case to underscores.
     * --
     * @param string $string
     * --
     * @return string
     */
    static function to_underscore($string)
    {
        validate::need_str($string);
        return self::to_lower(
            preg_replace('/(?<=[a-z])([A-Z])/', '_$1', $string)
        );
    }

    /**
     * Convert string to lowercase (unicode).
     * --
     * @param string $string
     * --
     * @return string
     */
    static function to_lower($string)
    {
        validate::need_str($string);
        return mb_strtolower($string);
    }

    /**
     * Convert string to uppercase (unicode).
     * --
     * @param  string $string
     * --
     * @return string
     */
    static function to_upper($string)
    {
        validate::need_str($string);
        return mb_strtoupper($string);
    }

    /**
     * Split string to segments (by string).
     * --
     * @param string  $string
     * @param string  $separator
     * @param integer $limit
     * --
     * @return array
     */
    static function split($string, $separator, $limit=null)
    {
        validate::need_str($string);
        validate::need_str($separator, 2);

        if ($limit !== null)
        {
            validate::need_int($limit);
            return explode($separator, $string, $limit);
        }
        else
        {
            return explode($separator, $string);
        }
    }

    /*
    --- Properties -------------------------------------------------------------
     */

    /**
     * Characters for normalization.
     * --
     * @var array
     */
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
}
