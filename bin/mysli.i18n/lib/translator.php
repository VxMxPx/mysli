<?php

namespace mysli\i18n; class translator
{
    const __use = '
        .{
            i18n,
            parser,
            exception.translator
        }
        mysli.toolkit.{
            json,
            fs.fs -> fs,
            fs.dir -> dir,
            fs.file -> file
        }
    ';

    /**
     * Primary language used for translations.
     * --
     * @var string
     */
    protected $primary;

    /**
     * Secondary language used for translations.
     * --
     * @var string
     */
    protected $secondary;

    /**
     * All appended dictionaries.
     * --
     * @var array
     */
    protected $dictionary = [];

    /**
     * Instantiate Translator.
     * --
     * @param string $primary
     * @param string $secondary
     */
    function __construct($primary, $secondary)
    {
        $this->primary   = $primary;
        $this->secondary = $secondary;
    }

    /**
     * Add new dictionary to the collection.
     * This will merge, rewriting duplicated values.
     * --
     * @param array $dictionary
     */
    function append(array $dictionary)
    {
        $this->dictionary = array_merge($this->dictionary, $dictionary);
    }

    /**
     * Load new dictionary and add it to the collection.
     * If neessary process it.
     *
     * Process:
     *
     * $path is package? => Resolve it and get full absolute path...
     * Exists $path/$language.json ? => Load
     * Exists $path/~dist/$language.json ? => Load
     * Exists tmp/i18n/qid_$language.json ? => Load
     *
     * Exists $path/$language.lng ? => Process => Save to Cache => Load
     * --
     * @param string $path
     *        Full absolute path or package's name to be auto resolved.
     *
     * @param string $language
     *        Language to load.
     *        If not provided both default laguages will be loaded.
     * --
     * @throws mysli\i18n\exception\translator 10 Invalid path.
     * @throws mysli\i18n\exception\translator 20 Invalid language format.
     * --
     * @return integer Number of successfully loaded dictionaries.
     */
    function load($path, $language=null)
    {
        // Process both defaults...
        if ($language === null)
        {
            $n = 0;
            foreach ([$this->primary, $this->secondary] as $language)
            {
                if ($language === null) continue;
                $n = $n + $this->load($path, $language);
            }
            return $n;
        }

        // Was package send in rather than path...
        if (preg_match('/[a-z0-9\.]+/i', $path))
            $path = i18n::get_path($path);

        if (!dir::exists($path))
            throw new exception\translator("Invalid path: `{$path}`.", 10);

        if (!preg_match('/^([a-z]{2}\-?){1,4}$/', $language))
            throw new exception\translator(
                "Invalid language format: `{$language}`.", 20
            );

        // Define paths
        $root_json = "{$path}/{$language}.json";
        $dist_json = "{$path}/~dist/{$language}.json";
        $tmp_json  = fs::tmppath('i18n', i18n::tmpname($language, $path));
        $root_src  = "{$path}/{$language}.lng";

        if (file::exists($root_json))
        {
            $dictionary = json::decode_file($root_json, true);
        }
        elseif (file::exists($dist_json))
        {
            $dictionary = json::decode_file($dist_json, true);
        }
        elseif (file::exists($tmp_json))
        {
            $dictionary = json::decode_file($tmp_json, true);
        }
        elseif (file::exists($root_src))
        {
            $lng = file::read($root_src);
            $dictionary = parser::process($lng, $language);
            json::encode_file($tmp_json, $dictionary);
        }
        else
        {
            return 0;
        }

        $this->append($dictionary);
        return 1;
    }

    /**
     * Check if particular language exists in dictionary.
     * --
     * @param  string $language
     * --
     * @return integer
     *         Number of keys for particular language,
     *         Null if language doesn't exists.
     */
    function exists($language)
    {
        if (isset($this->dictionary[$language]))
            return count($this->dictionary[$language]) - 1;
        else
            return;
    }

    /**
     * Set/get primary language for translations.
     * This will be automatically set, when the i18n/translator is constructed.
     * --
     * @param string $language
     *        If not provided then current value is returned.
     * --
     * @return string
     */
    function primary($language=null)
    {
        if ($language)
        {
            $this->primary = $language;
        }

        return $this->primary;
    }

    /**
     * Set/get secondary language for translations.
     * This will be automatically set, when the i18n/translator is constructed.
     * --
     * @param string $language
     *        If not provided then current value is returned.
     * --
     * @return string
     */
    function secondary($language=null)
    {
        if ($language)
        {
            $this->secondary = $language;
        }

        return $this->secondary;
    }

    /**
     * Return whole dictionary as an array.
     * --
     * @return array
     */
    function as_array()
    {
        return $this->dictionary;
    }

    /**
     * Translate the key, using particular language.
     * --
     * @param mixed $key
     *        Following options are available:
     *        - string: key, in format key | KEY
     *        - array : [key, switch], e.g., ['COMMENTS', $comments->count()]
     *
     * @param string $language
     *        E.g., en, ru
     *
     * @param mixed $variable
     *        Variables to be replaced in string.
     * --
     * @throws mysli\i18n\exception\translator 10 Badly formatted key.
     * --
     * @return string
     *         Null if key not found.
     */
    function translate_as($key, $language, $variable=[])
    {
        if (!is_array($variable))
            $variable = [$variable];

        if (is_array($key))
        {
            $modifier = $key[1];
            $key = $key[0];

            if (is_bool($modifier))
            {
                $modifier = $modifier ? 'true' : 'false';
            }
            else
            {
                $variable['n'] = $modifier;
            }
        }
        else
        {
            $modifier = false;
        }

        $key = strtoupper($key);

        // Non-existent language requested
        if (!$this->exists($language))
            return;

        $dictionary = &$this->dictionary[$language];

        // The key itself must be set for sure, no matter if we have modifier...
        if (isset($dictionary[$key]))
        {
            // No modifier? Return it right away...
            if ($modifier === false)
            {
                if (!isset($dictionary[$key]['value']))
                {
                    return;
                }

                return $this->process_variables(
                    $dictionary[$key]['value'], $variable
                );
            }
        }
        else
        {
            // Else, we'll deal with modifier later ...
            return;
        }

        // Simple check if modifier is already set as it is...
        if (isset($dictionary[$key][$modifier]))
        {
            if (!isset($dictionary[$key][$modifier]['value']))
            {
                return;
            }

            return $this->process_variables(
                $dictionary[$key][$modifier]['value'], $variable
            );
        }

        foreach ($dictionary[$key] as $dmod => $dval)
        {
            // Need to have value property!
            if (!isset($dval['value']))
            {
                continue;
            }
            else
            {
                $dval = $dval['value'];
            }

            // We already know it's not simple number, because if it would be,
            // we'd match it with isset statement above...
            if (is_numeric($dmod))
            {
                continue;
            }

            // Check for range
            if (strpos($dmod, '...') !== false)
            {
                $dmod = explode('...', $dmod, 2);

                if ($modifier >= $dmod[0] && $modifier <= $dmod[1])
                {
                    return $this->process_variables($dval, $variable);
                }
                else
                {
                    continue;
                }
            }

            // Check for greater than value...
            if (strpos($dmod, '+') !== false)
            {
                $dmod = (int) trim($dmod, '+');

                if ($modifier >= $dmod)
                {
                    return $this->process_variables($dval, $variable);
                }
                else
                {
                    continue;
                }
            }

            // Check for less than value...
            if (strpos($dmod, '-') !== false)
            {
                $dmod = (int) trim($dmod, '-');

                if ($modifier <= $dmod)
                {
                    return $this->process_variables($dval, $variable);
                }
                else
                {
                    continue;
                }
            }

            // Check for regex
            if (strpos($dmod, '*') !== false)
            {
                if (preg_match('/[^0-9\*]/', $dmod))
                {
                    throw new exception\translator(
                        "Badly formated key `{$dmod}[{$key}]` ".
                        "for language: `{$language}`. Allowed [0-9\\*].",
                        10
                    );
                }

                $dmod = str_replace('*', '.*?', $dmod);
                $dmod = "/^{$dmod}$/";

                if (preg_match($dmod, abs($modifier)))
                {
                    return $this->process_variables($dval, $variable);
                }
                else
                {
                    continue;
                }
            }
        }

        // No match!
        return;
    }

    /**
     * Translate the key!
     * --
     * @param mixed $key
     *        Following options are available:
     *        - string: key, in format key | KEY
     *        - array : [key, switch], e.g., ['COMMENTS', $comments->count()]
     *
     * @param mixed $variable
     *        Variables to be replaced in string.
     * --
     * @return string
     *         Null if key not found.
     */
    function translate($key, $variable=[])
    {
        if (!is_array($variable))
            $variable = [$variable];

        return $this->translate_as($key, $this->primary,   $variable)
            ?: $this->translate_as($key, $this->secondary, $variable);
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Process variables.
     * --
     * @param string $value
     * @param array  $variables
     * --
     * @return string
     */
    protected function process_variables($value, array $variables)
    {
        if (empty($variables))
        {
            return $value;
        }

        // Process variables here...
        foreach ($variables as $id => $var)
        {
            // Allowed are only numeric values, and 'n' which represent modifier
            if (is_numeric($id))
            {
                $id = (int) $id + 1;
            }
            elseif ($id !== 'n')
            {
                continue;
            }

            $value = preg_replace_callback(
                '/{'.$id.' ?(.*?)}/',
                function ($match) use ($var)
                {
                    if (isset($match[1]))
                    {
                        return sprintf($var, $match[1]);
                    }
                    else
                    {
                        return $var;
                    }
                },
                $value
            );
        }

        return $value;
    }
}
