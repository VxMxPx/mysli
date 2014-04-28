<?php

namespace Mysli\I18n;

class I18n
{
    private $package;
    private $filename;
    private $config;

    private $translator;

    /**
     * Create i18n instance.
     * --
     * @param array  $pkgm_trace
     * @param object $config
     */
    public function __construct(array $pkgm_trace, $config)
    {
        $this->package = array_pop($pkgm_trace)[0];

        // Get filename
        $this->filename = str_replace('/', '.', $this->package);
        $this->filename = datpath('mysli.i18n', $this->filename . '.json');

        $this->config = $config;
    }

    /**
     * Return translator object.
     * --
     * @return object \Mysli\I18n\Translator
     */
    public function translator()
    {
        if (!$this->translator) {
            // If we have file, then load contents...
            $dictionary = [];
            if (file_exists($this->filename)) {
                $dictionary = \Core\JSON::decode_file($this->filename, true);
            }
            $this->translator = new \Mysli\I18n\Translator(
                $dictionary,
                $this->config->get('primary_language'),
                $this->config->get('secondary_language')
            );
        }

        return $this->translator;
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
            $collection[substr($file, 0, -3)] = \Mysli\I18n\Parser::parse(
                file_get_contents(ds($dir, $file))
            );
        }

        // $this->dictionary = $collection;
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

}
