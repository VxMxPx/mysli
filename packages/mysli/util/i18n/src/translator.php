<?php

namespace mysli\util\i18n;

__use(__namespace__, '
    mysli.framework.exception/* -> framework\exception\*
');

class translator {

    protected $primary;
    protected $secondary;

    protected $dictionary = [];

    function __construct($dictionary, $primary, $secondary) {
        $this->dictionary = $dictionary;
        $this->primary    = $primary;
        $this->secondary  = $secondary;
    }
    /**
     * Check if particular language exists in cache.
     * @param  string $language
     * @return integer Number of keys for particular language,
     *                 0 if language doesn't exists.
     */
    function exists($language) {
        if (isset($this->dictionary[$language])) {
            return count($this->dictionary[$language]) - 1;
        } else return 0;
    }
    /**
     * Set/get primary language for translations.
     * This will be automatically set, when the i18n/translator is constructed.
     * @param string $language if not set then current value is returned
     * @return string
     */
    function primary($language=null) {
        if ($language) {
            $this->primary = $language;
        }
        return $this->primary;
    }
    /**
     * Set/get secondary language for translations.
     * This will be automatically set, when the i18n/translator is constructed.
     * @param string $language if not set then current value is returned
     * @return string
     */
    function secondary($language=null) {
        if ($language) {
            $this->secondary = $language;
        }
        return $this->secondary;
    }
    /**
     * Return whole dictionary as an array.
     * @return array
     */
    function as_array() {
        return $this->dictionary;
    }
    /**
     * Translate the key, using particular language.
     * @param  mixed  $key      Following options are available:
     *   - string: key, in format key | KEY
     *   - array : [key, switch], e.g., ['COMMENTS', $comments->count()]
     * @param  string $language e.g., en, ru
     * @param  mixed  $variable Variables to be replaced in string.
     * @return string, null if key not found!
     */
    function translate_as($key, $language, $variable=[]) {
        if (!is_array($variable)) {
            $variable = [$variable];
        }
        if (is_array($key)) {
            $modifier = $key[1];
            $key = $key[0];
            if (is_bool($modifier)) {
                $modifier = $modifier ? 'true' : 'false';
            } else {
                $variable['n'] = $modifier;
            }
        } else {
            $modifier = false;
        }

        $key = strtoupper($key);

        // Non-existent language requested
        if (!$this->exists($language)) {
            return null;
        }

        $dictionary = &$this->dictionary[$language];

        // The key itself must be set for sure, no matter if we have modifier...
        if (isset($dictionary[$key])) {
            // No modifier? Return it right away...
            if ($modifier === false) {
                if (!isset($dictionary[$key]['value'])) return null;
                return $this->process_variables($dictionary[$key]['value'],
                                                $variable);
            }
            // Else, we'll deal with modifier later ...
        } else {
            return null;
        }

        // Simple check if modifier is already set as it is...
        if (isset($dictionary[$key][$modifier])) {
            if (!isset($dictionary[$key][$modifier]['value'])) return null;
            return $this->process_variables(
                $dictionary[$key][$modifier]['value'],
                $variable
            );
        }

        foreach ($dictionary[$key] as $dmod => $dval) {
            // Need to have value property!
            if (!isset($dval['value'])) continue;
            else $dval = $dval['value'];
            // We already know it's not simple number, because if it would be,
            // we'd match it with isset statement above...
            if (is_numeric($dmod)) continue;

            // Check for range
            if (strpos($dmod, '...') !== false) {
                $dmod = explode('...', $dmod, 2);
                if ($modifier >= $dmod[0] && $modifier <= $dmod[1]) {
                    return $this->process_variables($dval, $variable);
                } else continue;
            }

            // Check for greater than value...
            if (strpos($dmod, '+') !== false) {
                $dmod = (int) trim($dmod, '+');
                if ($modifier >= $dmod) {
                    return $this->process_variables($dval, $variable);
                } else continue;
            }

            // Check for less than value...
            if (strpos($dmod, '-') !== false) {
                $dmod = (int) trim($dmod, '-');
                if ($modifier <= $dmod) {
                    return $this->process_variables($dval, $variable);
                } else continue;
            }

            // Check for regex
            if (strpos($dmod, '*') !== false) {
                if (preg_match('/[^0-9\*]/', $dmod)) {
                    throw new framework\exception\data(
                        "Badly formated key `{$dmod}[{$key}]` ".
                        "for language: `{$language}`. Allowed [0-9\\*].",
                        1);
                }
                $dmod = str_replace('*', '.*?', $dmod);
                $dmod = "/^{$dmod}$/";
                if (preg_match($dmod, abs($modifier))) {
                    return $this->process_variables($dval, $variable);
                } else continue;
            }
        }

        // No match!
        return null;
    }
    /**
     * Translate the key!
     * @param  mixed $key      Following options are available:
     *   - string: key, in format key | KEY
     *   - array : [key, switch], e.g., ['COMMENTS', $comments->count()]
     * @param  mixed $variable Variables to be replaced in string.
     * @return string, null if key not found!
     */
    function translate($key, $variable=[]) {
        if (!is_array($variable)) $variable = [$variable];

        return
            $this->translate_as($key, $this->primary,   $variable) ?:
            $this->translate_as($key, $this->secondary, $variable);
    }

    /**
     * Process variables.
     * @param  string $value
     * @param  array  $variables
     * @return string
     */
    private function process_variables($value, array $variables) {
        if (empty($variables)) {
            return $value;
        }

        // Process variables here...
        foreach ($variables as $id => $var) {
            // Allowed are only numeric values, and 'n' which represent modifier
            if (is_numeric($id)) $id = (int) $id + 1;
            elseif ($id !== 'n') continue;

            $value = preg_replace_callback('/{'.$id.' ?(.*?)}/',
            function ($match) use ($var) {
                if (isset($match[1])) {
                    return sprintf($var, $match[1]);
                } else return $var;
            }, $value);
        }

        return $value;
    }
}
