<?php

namespace Mysli;

class I18n
{
    protected $cache = [];

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
        // costumized config, containing only element meant for package, which
        // required config.
        array_pop($pkgm_trace); // Remove self
        $this->package = array_pop($pkgm_trace); // Get actual package which required config.

        // Get filename
        $this->filename = str_replace('/', '.', $this->package);
        $this->filename = datpath('i18n', $this->filename . '.json');

        // If we have file, then load contents...
        if (file_exists($this->filename)) {
            $this->cache = \Core\JSON::decode_file($this->filename, true);
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
        if (isset($this->cache[$language])) {
            return count($this->cache[$language]);
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
     * Return cache as an array.
     * --
     * @return array
     */
    public function cache_as_array()
    {
        return $this->cache;
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

        $this->cache = $collection;
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
     * Translate the key!
     * --
     * @param  mixed $key      Following options are available:
     *   - string: key, in format key | KEY
     *   - array : [key, switch], e.g., ['COMMENTS', $comments->count()]
     * @param  array $variable Variables to be replaced in string.
     * --
     * @return string, null if key not found!
     */
    public function translate($key, array $variable = [])
    {

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
            '/(^@[A-Z_]+)(\[[0-9\>a-z,]+\])?[\ \t\n]+(.*?)(?=^@|^#)/sm',
            $mt,
            $matches,
            PREG_SET_ORDER
        );

        foreach ($matches as $match) {
            if ($match[2]) {
                $options = trim($match[2], '[]');
                $options = \Core\Str::explode_trim(',', $options);
            } else {
                $options = [];
            }
            $key   = trim($match[1], '@');
            $value = trim($match[3]);
            if (!in_array('nl', $options)) {
                $value = str_replace("\n", ' ', $value);
            }
            if (!in_array('html', $options)) {
                $value = htmlentities($value);
            }
            foreach ($options as $option) {
                if (
                    is_numeric($option) ||
                    in_array($option, ['true', 'false', '>'])
                ) {
                    $collection[$key][$option]['value'] = $value;
                    continue 2; // Continue outer loop
                }
            }
            $collection[$key]['value'] = $value;
        }

        return $collection;
    }
}
