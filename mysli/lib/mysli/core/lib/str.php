<?php

namespace Mysli\Core\Lib;

class Str
{
    // Cache, set by method: symbols_to_words
    protected static $symbols_list = null;

    // Cache, set by method: normalize
    protected static $char_list = null;

    /**
     * Limit characters repetition.
     * Using $char='!', $limit=1
     * 'hello world!!!!' ----> 'hello world!'
     * Using $char=[' ', '!'], $limit=2
     * 'hello    world!!!!!!' ----> 'hello  world!!'
     * --
     * @param  string  $input
     * @param  mixed   $char  string, one char, or array of characters we want
     *                        to limit.
     * @param  integer $limit to how many characters should te limit be set
     * @return string
     */
    public static function limit_repeat($input, $char, $limit)
    {
        if (is_array($char)) {
            foreach ($char as $char_item) {
                try {
                    $input = self::limit_repeat($input, $char_item, $limit);
                }
                catch (\Avrelia\Exception\ValueError $e) {
                    throw new $e;
                }
            }
            return $input;
        }

        if (strlen($char) < 1) {
            throw new \Avrelia\Exception\ValueError(
                "Expected parameter is string, long at least one character.", 1);

        }

        $char_escaped = preg_quote($char);

        $limit = (int) $limit;

        if ($limit < 1) {
            throw new \Avrelia\Exception\ValueError(
                "Expected parameter is integer higher than one.", 2);
        }

        $regex = "([{$char_escaped}]{{$limit},})";

        $input = preg_replace($regex, str_repeat($char, $limit), $input);

        return $input;
    }

    /**
     * Convert signs (like €, $, #) to regular words.
     *
     * @param   string  $string
     * @return  string
     */
    public static function symbols_to_words($string)
    {
        if (!is_array(self::$symbols_list)):
            self::$symbols_list = array(
            '’' => 'apostrophe',                 "'" => 'apostrophe',
            '[' => 'left square bracket',        ']' => 'right square bracket',
            '(' => 'left bracket',               ')' => 'right bracket',
            '{' => 'left curly bracket',         '}' => 'right curly bracket',
            ':' => 'colon',                      ',' => 'comma',
            '‒' => 'dash',                       '–' => 'dash',
            '—' => 'dash',                       '―' => 'dash',
            '…' => 'ellipsis',                   '...' => 'ellipsis',
            '. . .' => 'ellipsis',               '!' => 'exclamation',
            '.' => 'period',                     '«' => 'left guillemet',
            '»' => 'right guillemet',            '-' => 'minus',
            '?' => 'question',                   '‘' => 'left quote',
            '’' => 'right quote',                '“' => 'left quote',
            '”' => 'right quote',                '“' => 'left quote',
            '"' => 'quote',                      ';' => 'semicolon',
            '/' => 'slash‌',                      '⁄' => 'slash‌',
            ' ' => 'space',                      '·' => 'interpunct',
            '&' => 'and',                        '@' => 'at',
            '*' => 'asterisk',                   '\\' => 'backslash',
            '•' => 'bullet',                     '^' => 'caret',
            '†' => 'dagger',                     '‡' => 'dagger',
            '°' => 'degree',                     '〃' => 'ditto',
            '¡' => 'inverted exclamation',       '¿' => 'inverted question',
            '#' => 'hash',                       '№' => 'numero',
            '÷' => 'obelus',                     'º' => 'ordinal',
            'ª' => 'ordinal',                    '%' => 'percent',
            '‰' => 'per mil',                    '‱' => 'per mil',
            '¶' => 'pilcrow',                    '′' => 'prime',
            '″' => 'prime',                      '‴' => 'prime',
            '§' => 'section',                    '+' => 'plus',
            '=' => 'equal',                      '<' => 'less than',
            '>' => 'more than',                  '~' => 'tilde',
            '_' => 'underscore',                 '|' => 'pipe',
            '¦' => 'pipe',                       '©' => 'copyright',
            '®' => 'registered trademark',       '℠' => 'service mark',
            '℗' => 'sound recording copyright', '™' => 'trademark',
            '¤' => 'currency',                   '⁂' => 'asterism',
            '⊤' => 'tee',                       '⊥' => 'up tack',
            '☞' => 'index',                     '∴' => 'therefore',
            '∵' => 'because',                    '‽' => 'interrobang',
            '◊' => 'lozenge',                    '※' => 'reference',
            '⁀' => 'tie',                       '¢' => 'cent',
            '$' => 'dollar',                     '€' => 'euro',
            );
        endif;

        return strtr($string, self::$symbols_list);
    }

    /**
     * Generate random string
     *
     * @param   integer $length
     * @param   string  $mask
     *              a ----> qwertzuiopasdfghjklyxcvbnm
     *              A ----> QWERTZUIOPASDFGHJKLYXCVBNM
     *              1 ----> 0123456789
     *              s ----> ~#$%&()=?*<>-_:.;,+!
     * @return  string
     */
    public static function random($length, $mask='aA1s')
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

        $i = 1;
        $result = '';

        while ($i <= $length) {
            $result .= $chars{mt_rand(0,strlen($chars)-1)};
            $i++;
        }

        return $result;
    }

    /**
     * Standardize line endings to \n
     * --
     * @param   string  $input
     * @return  string
     */
    public static function standardize_line_endings($input)
    {
        return preg_replace('/\r{,2}|\n{,2}|\r\n{,2}/ism', "\n", $input);
    }

    /**
     * Convert string to slug
     *     "Hello world" ----> "hello-world"
     *
     * @param   string  $string
     * @param   array   $delimiter
     * @return  string
     */
    public static function slug($string, $delimiter='-')
    {
        if (empty($string)) { return ''; }
        $string = mb_strtolower($string, 'UTF-8');

        // This Will Also normalize chatacters e.g: č => c
        $string = self::clean($string, 'aA1s', '-_');
        $string = preg_replace('/( |_|-)+/', $delimiter, $string);

        return $string;
    }

    /**
     * Convert string to unique slug, you must provide list of existing slugs.
     *     "Hello world" ----> "hello-world"
     *
     * @param   string  $string
     * @param   string  $slugs_list
     * @param   array   $delimiter
     * @return  string
     */
    public static function slug_unique($string, $slugs_list, $delimiter='-')
    {
        $string = self::slug($string, $delimiter);
        if (Arr::is_empty($slugs_list)) { return $string; }

        $num = 1;
        $base_string = $string;

        while (in_array($string, $slugs_list)) {
            $string = $base_string . $delimiter . $num;
            $num++;
        }

        return $string;
    }

    /**
     * Clean string data
     *
     * @param   string  $string
     * @param   string  $mask   aA1s ---->  small a-z, up A-Z, numeric, spaces
     * @param   string  $custom custom characters ----> ,-+*!?#
     *                          Note: custom can be omitted!
     * @param   integer $limit
     * @return  string
     */
    public static function clean($string, $mask='aA1s', $custom=false, $limit=256)
    {
        if (empty($string)) { return ''; }

        // Normalize String
        $string = self::normalize($string);

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
        }
        else
            { throw new \Avrelia\Exception\ValueError('Invalid or empty mask.', 1); }

        if (empty($filter))
            { throw new \Avrelia\Exception\ValueError('Empty filter.', 2); }

        // Construct regular expression filter
        $filter = '/([^' . $filter . '])/sm';

        $new_string = preg_replace($filter, '', $string);

        if ((int) $limit)
            { $new_string = substr($new_string, 0, (int) $limit); }

        return $new_string;
    }

    /**
     * Clean string data with the help of regular expression. Removes matches.
     *
     * @param  string  $string
     * @param  string  $regex
     * @return string
     */
    public static function clean_regex($string, $regex)
    {
        return preg_replace($regex, '', $string);
    }

    /**
     * Normalize to Clean Latin characters, for example convert ščž => scz
     *
     * @param   string  $string
     * @return  string
     */
    public static function normalize($string)
    {
        if (!self::$char_list):
            self::$char_list = array(
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae',
            'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae',
            'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C',
            'Ď' => 'D', 'Đ' => 'D', 'Ð' => 'D',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E',
            'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E',
            'Ĝ' => 'G', 'Ğ' => 'G', 'Ġ' => 'G', 'Ģ' => 'G',
            'Ĥ' => 'H', 'Ħ' => 'H',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I',
            'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I', 'İ' => 'I', 'Ĳ' => 'Ij', 'Ĵ' => 'J',
            'Ķ' => 'K',
            'Ł' => 'L', 'Ľ' => 'L', 'Ĺ' => 'L', 'Ļ' => 'L', 'Ŀ' => 'L',
            'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N', 'Ņ' => 'N', 'Ŋ' => 'N',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'Oe',
            'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O', 'Œ' => 'Oe',
            'Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R',
            'Ś' => 'S','Š' => 'S','Ş' => 'S','Ŝ' => 'S','Ș' => 'S',
            'Ť' => 'T','Ţ' => 'T','Ŧ' => 'T','Ț' => 'T',
            'Ù' => 'U','Ú' => 'U','Û' => 'U','Ü' => 'Ue','Ū' => 'U',
            'Ů' => 'U','Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U',
            'Ŵ' => 'W',
            'Ý' => 'Y','Ŷ' => 'Y','Ÿ' => 'Y','Y' => 'Y',
            'Ź' => 'Z','Ž' => 'Z','Ż' => 'Z',
            'Þ' => 'T',
            'à' => 'a','á' => 'a','â' => 'a','ã' => 'a','ä' => 'ae','å' => 'a',
            'ā' => 'a','ą' => 'a','ă' => 'a','æ' => 'ae',
            'ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
            'ď' => 'd','đ' => 'd','ð' => 'd',
            'è' => 'e','é' => 'e','ê' => 'e','ë' => 'e','ē' => 'e','ę' => 'e',
            'ě' => 'e','ĕ' => 'e','ė' => 'e',
            'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g',
            'ĥ' => 'h','ħ' => 'h',
            'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
            'ĭ' => 'i','į' => 'i','ı' => 'i',
            'ĳ' => 'ij','ĵ' => 'j',
            'ķ' => 'k',
            'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l',
            'ñ' => 'n','ń' => 'n','ň' => 'n','ņ' => 'n','ŋ' => 'n',
            'ò' => 'o','ó' => 'o','ô' => 'o','õ' => 'o','ö' => 'oe','ø' => 'o',
            'ō' => 'o','ő' => 'o','ŏ' => 'o','œ' => 'oe',
            'ŕ' => 'r','ř' => 'r','ŗ' => 'r',
            'ś' => 's','š' => 's','ş' => 's','ŝ' => 's','ș' => 's',
            'ť' => 't','ţ' => 't','ŧ' => 't','ț' => 't',
            'ù' => 'u','ú' => 'u','û' => 'u','ü' => 'ue','ū' => 'u','ů' => 'u',
            'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u',
            'ŵ' => 'w',
            'ý' => 'y','ŷ' => 'y','ÿ' => 'y','y' => 'y',
            'ź' => 'z','ž' => 'z','ż' => 'z',
            'þ' => 't','ß' => 'ss','ſ' => 'ss','ƒ' => 'f','ĸ' => 'k','ŉ' => 'n',
            );
        endif;

        return strtr($string, self::$char_list);
    }

    /**
     * Get desired number of words - shorten string nicely.
     * --
     * @param   string  $input
     * @param   integer $limit
     * @param   string  $ending
     * @return  string
     */
    public static function limit_words($input, $limit, $ending=null)
    {
        $input = (string) $input;

        // Check if limit < 1
        if ($limit < 1) {
            throw new \Avrelia\Exception\ValueError(
                'Limit must be at least one character!', 1);
        }

        $input_original = $input;
        $input = explode(' ', $input);
        $final = ''; $i = 0;
        if (is_array($input)) {
            foreach ($input as $word)
            {
                $final .= $word . ' ';

                if ($i >= $limit)
                    { break; }
                else
                    { $i++; }
            }
        }
        else {
            return $input;
        }

        if ($ending && strlen($input_original) !== strlen($final))
            { $final = $final . $ending; }

        return rtrim($final);
    }

    /**
     * Get desired number of characters.
     * --
     * @param   string  $input
     * @param   integer $limit
     * @param   string  $ending
     * @return  string
     */
    public static function limit_length($input, $limit, $ending=null)
    {
        // Adjust length if we have special ending
        if ($ending) { $limit = $limit - strlen($ending); }
        $input = (string) $input;
        $input_original = $input;

        // Check if limit < 1
        if ($limit < 1) {
            throw new \Avrelia\Exception\ValueError(
                'Limit must be at least one character!', 1);
        }

        $input = substr($input, 0, $limit);

        if ($ending && strlen($input_original) !== strlen($input))
            { $final = $final . $ending; }

        return $input;
    }

    /**
     * Explode and trim data.
     *
     * @param  string  $separator If array, then we'll explode by all of them!
     * @param  string  $input
     * @param  string  $trim_mask
     * @param  integer $limit
     * @return array
     */
    public static function explode_trim($separator, $input, $trim_mask='', $limit=false)
    {
        // If we wanna replace by more than one item!
        if (is_array($separator)) {
            $sep_first = $separator[0];
            unset($separator[0]);

            foreach ($separator as $sep) {
                str_replace($sep, $sep_first, $input);
            }

            $separator = $sep_first;
        }

        if ($limit !== false)
            { $d = explode($separator, $input, $limit); }
        else
            { $d = explode($separator, $input); }

        $f = array();

        if (is_array($d))
            { foreach($d as $i) { $f[] = trim($i, $trim_mask); } }

        return $f;
    }

    /**
     * Explode and trim data, and get particular index.
     *
     * @param  string  $separator If array, then we'll explode by all of them!
     * @param  string  $input
     * @param  integer $piece_index
     * @param  integer $limit
     * @return array or false
     */
    public static function explode_get($separator, $input, $piece_index, $limit=false)
    {
        $return = self::explode_trim($separator, $input, $limit);
        return isset($return[$piece_index]) ? $return[$piece_index] : false;
    }

    /**
     * Explode string by separator, but ignore protected regions.
     *     id='head' class='odd new' title='it\'s a nice day!' ---->
     * Space as a separator and array() as protected:
     *     [["id='head'"], ["class='odd new'"], ["title='it\'s a nice day!'"]]
     *
     * @param  string $input
     * @param  string $separator
     * @param  array  $protected Protected regions. As single character, when
     *                           same end as start, or array, with open and
     *                           end tag.
     * @return array
     */
    public static function tokenize($input, $separator, $protected)
    {
        // Check if input is string
        if (!is_string($input) || empty($input))
            {  return false; }

        // Check if separator isn't just \ character
        if ($separator === CHAR_BACKSLASH) {
            Log::war("Separator can't be backslash.");
            return false;
        }

        // Open and close of protected region
        if (is_array($protected)) {
            if (count($protected) !== 2) {
                Log::war("Protected need to have exactly 2 elements.");
                return false;
            }

            $protected_open = $protected[0];
            $protected_close = $protected[1];
        }
        else {
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
        for ($i=0; $i<$input_length; $i++) {
            $current_char = $input[$i];

            switch ($current_char) {
                case CHAR_BACKSLASH:
                    $is_escaped = true;
                    $current_token .= CHAR_BACKSLASH;
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
     * @param  mixed   $input      String or array
     * @param  mixed   $disallowed String, array or associative array
     * @param  string  $mask       Mask character
     * @param  mixed   $keep       Integer, or array consisting of two numbers,
     *                             1: characters to keep on left,
     *                             2: characters to keep on right
     * @return mixed   Depends on input type.
     */
    public static function censor($input, $disallowed, $mask='*', $keep=2)
    {
        // We need to have input of course...
        if (empty($input)) { return $input; }

        // Do we have an array as input?
        if (is_array($input)) {
            $result = array();
            foreach ($input as $k => $value) {
                $result[$k] = self::censor($value, $disallowed, $mask, $keep);
            }
            return $result;
        }

        // Disallowed must be an array
        if (!is_array($disallowed)) { $disallowed = array($disallowed); }

        // Set keep ranges
        if (is_array($keep)) {
            $keep_left  = Arr::element(0, $keep, 0);
            $keep_right = Arr::element(1, $keep, false);
            $keep_right = $keep_right ? -($keep_right) : false;
        }
        else {
            $keep_left  = $keep;
            $keep_right = false;
        }

        $keep = array();
        $keep[0] = $keep_left;
        $keep[1] = $keep_right;

        foreach ($disallowed as $censor => $word) {
            $regex = '/\b('.preg_quote($word).')\b/i';
            $input = preg_replace_callback(
            $regex,
            function($match) use ($censor, $mask, $keep) {
                if (!is_integer($censor)) {
                    return $censor;
                }
                else if (!$keep[0]) {
                    return str_repeat($mask, strlen($match[0]));
                }
                else {
                    $end = $keep[1] ? $keep[1] : strlen($match[0]);
                    $mask_length = strlen($match[0]);
                    $mask_length = $mask_length - $keep[0];
                    $mask_length = $mask_length + $keep[1];
                    if ($mask_length >= 0) {
                        $mask_full = str_repeat($mask, $mask_length);
                    }
                    else {
                        return str_repeat($mask, strlen($match[0]));
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
    public static function to_camelcase($string, $uc_first=true)
    {

        // Convert _
        if (strpos($string, '_') !== false) {
            $string = str_replace('_', ' ', $string);
            $string = ucwords($string);
            $string = str_replace(' ', '', $string);
        }

        // Convert backslashes
        if (strpos($string, CHAR_BACKSLASH) !== false) {
            $string = str_replace(CHAR_BACKSLASH, ' ', $string);
            $string = ucwords($string);
            $string = str_replace(' ', CHAR_BACKSLASH, $string);
        }

        // Convert slashes
        if (strpos($string, CHAR_SLASH) !== false) {
            $string = str_replace(CHAR_SLASH, ' ', $string);
            $string = ucwords($string);
            $string = str_replace(' ', CHAR_SLASH, $string);
        }

        if (!$uc_first) {
            $string = lcfirst($string);
        }
        else {
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