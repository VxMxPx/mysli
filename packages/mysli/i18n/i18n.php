<?php

namespace Mysli;

class I18n
{
    protected $dictionary = [];

    protected $package;
    protected $filename;

    protected $primary_language;
    protected $fallback_language;

    /**
     * Create i18n instance.
     * --
     * @param array  $pkgm_trace
     * @param object $config     ~config
     */
    public function __construct(array $pkgm_trace, $config)
    {
        // Pkgm trace is array, list of packages, which required this package.
        // In this case, we'll use this info, to construct
        // customized config, containing only element meant for package, which
        // required config.
        array_pop($pkgm_trace); // Remove self
        $this->package = array_pop($pkgm_trace); // Get actual package which required config.

        // Get filename
        $this->filename = str_replace('/', '.', $this->package);
        $this->filename = datpath('i18n', $this->filename . '.json');

        // If we have file, then load contents...
        if (file_exists($this->filename)) {
            $this->dictionary = \Core\JSON::decode_file($this->filename, true);
        }

        // Set primary and secondary language
        $this->primary_language = $config->get('primary_language');
        $this->fallback_language = $config->get('fallback_language');
    }

    /**
     * Check if particular language exists in cache.
     * --
     * @param  string $language
     * --
     * @return integer Number of keys for particular language,
     *                 0 if language doesn't exists.
     */
    public function exists($language)
    {
        if (isset($this->dictionary[$language])) {
            return count($this->dictionary[$language]) - 1;
        } else return 0;
    }

    /**
     * Set primary language for translations. This will be automatically set,
     * when the i18n is constructed (value read from settings + event triggered).
     * --
     * @param string $language
     * --
     * @return null
     */
    public function set_language($language)
    {
        $this->primary_language = $language;
    }

    /**
     * Set fallback language, if primary not found. This will be automatically set,
     * when the i18n is constructed (value read from settings + event triggered).
     * --
     * @param string $language
     * --
     * @return null
     */
    public function set_fallback_language($language)
    {
        $this->fallback_language = $language;
    }

    /**
     * Return dictionary as an array.
     * --
     * @return array
     */
    public function as_array()
    {
        return $this->dictionary;
    }

    /**
     * Create cache for current package.
     * --
     * @return boolean
     */
    public function cache_create($folder = 'i18n')
    {
        // pkgpath is packages path, function defined by ~core!
        $dir = pkgpath($this->package, $folder);
        if (!file_exists($dir)) {
            throw new \Core\NotFoundException(
                "Cannot create cache. Directory doesn't exists: `{$dir}`.", 1
            );
        }

        $collection = [];

        $files = scandir($dir);
        foreach ($files as $file) {
            if (substr($file, -3) !== '.mt') { continue; }
            $collection[substr($file, 0, -3)] = $this->mt_to_array(
                file_get_contents(ds($dir, $file))
            );
        }

        $this->dictionary = $collection;
        return \Core\JSON::encode_file($this->filename, $collection);
    }

    /**
     * Remove cache for current package.
     * --
     * @return boolean
     */
    public function cache_remove()
    {
        if (file_exists($this->filename)) {
            return unlink($this->filename);
        } else return true;
    }

    /**
     * Process variables.
     * --
     * @param  string $value
     * @param  array  $variables
     * --
     * @return string
     */
    protected function process_variables($value, array $variables)
    {
        if (empty($variables)) {
            return $value;
        }

        // Process variables here...
        foreach ($variables as $id => $var) {
            // Allowed are only numeric values, and 'n' which represent modifier
            if (is_numeric($id)) $id = (int) $id + 1;
            elseif ($id !== 'n') continue;

            $value = preg_replace_callback('/{'.$id.' ?(.*?)}/', function ($match) use ($var) {
                if (isset($match[1])) {
                    return sprintf($var, $match[1]);
                } else return $var;
            }, $value);
        }

        return $value;
    }

    /**
     * Translate the key, using particular language.
     * --
     * @param  mixed  $key      Following options are available:
     *   - string: key, in format key | KEY
     *   - array : [key, switch], e.g., ['COMMENTS', $comments->count()]
     * @param  string $language e.g., en, ru
     * @param  mixed  $variable Variables to be replaced in string.
     * --
     * @throws \Core\DataException If TRANSLATION[key][modifier_with_*] is badly formatted.
     * --
     * @return string, null if key not found!
     */
    public function translate_as($key, $language, $variable = [])
    {
        if (!is_array($variable)) $variable = [$variable];

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
                return $this->process_variables($dictionary[$key]['value'], $variable);
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
                    throw new \Core\DataException(
                        "Badly formated key `{$dmod}[{$key}]` ".
                        "for language: `{$language}`. Allowed [0-9\\*].",
                        1
                    );
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
     * --
     * @param  mixed $key      Following options are available:
     *   - string: key, in format key | KEY
     *   - array : [key, switch], e.g., ['COMMENTS', $comments->count()]
     * @param  mixed $variable Variables to be replaced in string.
     * --
     * @return string, null if key not found!
     */
    public function translate($key, $variable = [])
    {
        if (!is_array($variable)) $variable = [$variable];

        return
            $this->translate_as($key, $this->primary_language,  $variable) ?:
            $this->translate_as($key, $this->fallback_language, $variable);
    }

    /**
     * Convert Mysli Translation (mt) to array.
     * --
     * @param  string $mt
     * --
     * @return array
     */
    public function mt_to_array($mt)
    {
        $matches;
        $collection = [
            '.meta' => [
                'created_on' => gmdate('YmdHis'),
                'modified'   => false
            ]
        ];

        // Append EOF to the end of string, so that we'll get the last match
        $mt .= "\n# EOF";

        // Standardize line endings
        $mt = \Core\Str::to_unix_line_endings($mt);

        // Match
        preg_match_all(
            '/(^@[A-Z_]+)(\[[0-9\*\+\-\.a-z,]+\])?[\ \t\n]+(.*?)(?=^@|^#)/sm',
            $mt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            // Assign key and value
            $key   = trim($match[1], '@');
            $value = trim($match[3]);

            $options = trim($match[2], '[]');
            if ($options === '') $options = [];
            else $options = \Core\Str::explode_trim(',', $options);

            if (in_array('nl', $options)) {
                $options = \Core\Arr::delete_by_value_all('nl', $options);
            } else {
                // Eliminate new-lines
                $value = str_replace("\n", ' ', $value);
            }

            if (empty($options)) {
                $collection[$key]['value'] = $value;
            } else {
                foreach ($options as $option) {
                    if ($option === '') continue;
                    $collection[$key][$option]['value'] = $value;
                }
            }
        }

        return $collection;
    }
}
