<?php

namespace Mysli;

class Tplp
{
    /**
     * Package which required tplp
     * @var string
     */
    private $package;

    /**
     * Directory where the cache will be/is located.
     * @var string
     */
    private $cache_dir;

    /**
     * Instance of Tplp
     * --
     * @param array $pkgm_trace
     */
    public function __construct(array $pkgm_trace)
    {
        array_pop($pkgm_trace); // Remove self
        $this->package = array_pop($pkgm_trace); // Get actual package which required config.
        $this->cache_root = datpath('tplp', str_replace('/', '.', $this->package));
    }


    /**
     * Get template content, standardize line endings.
     * --
     * @param  string $filename
     * --
     * @return string
     */
    private function template_get_content($filename)
    {
        $template = file_get_contents($filename);
        $template = \Core\Str::to_unix_line_endings($template);

        return $template;
    }

    /**
     * Find all template files in particular directory.
     * --
     * @param  string $root Directory to be searched.
     * @param  string $sub  Sub directory (used root + sub) mostly for recursion.
     * --
     * @return array
     */
    private function templates_find($root, $sub = null)
    {
        $templates = [];

        $files = scandir(ds($root, $sub));
        $files = array_diff($files, ['.', '..']);

        foreach ($files as $file) {

            if (is_dir(ds($root, $sub, $file))) {
                $templates = array_merge(
                    $templates,
                    $this->templates_find($root, ds($sub, $file))
                );
            }

            if (substr($file, -10) !== '.tplm.html') {
                continue;
            }

            $handler = trim(ds($sub, substr($file, 0, -10)), '/');
            $templates[$handler] = $this->template_get_content(ds($root, $sub, $file));
        }

        return $templates;
    }

    /**
     * Get template (object) by name.
     * --
     * @param  string $name
     * --
     * @return object \Mysli\Tplp\Template
     */
    public function template($name)
    {
        return new \Mysli\Tplp\Template($this->package, $name);
    }

    /**
     * Create cache for package.
     * --
     * @param string $folder Templates root folder (relative).
     * --
     * @throws \Core\NotFoundException If templates directory couldn't be found.
     * --
     * @return integer Number of created files.
     */
    public function cache_create($folder = 'templates')
    {
        // Check if templates dir exists...
        $templates_dir = pkgpath($this->package, $folder);

        if (!is_dir($templates_dir)) {
            throw new \Core\NotFoundException(
                "Cannot find templates directory: '{$templates_dir}'.", 1
            );
        }

        $templates = $this->templates_find($templates_dir);

        // Create cache directory if not there already
        if (!file_exists($this->cache_root)) {
            \Core\FS::dir_create($this->cache_root);
        }

        // Parse all templates...
        foreach ($templates as $handle => &$template) {
            $parser = new \Mysli\Tplp\Parser(
                file_get_contents(ds($templates_dir, $handle . '.tplm.html'))
            );
            $template = $parser->parse();
        }

        // Resolve templates' inner dependencies (::use <file>)
        $resover = new \Mysli\Tplp\InclusionsResolver($templates);
        $templates = $resover->resolve();

        // Save files
        $created = 0;
        foreach ($templates as $handle => $template) {
            $cache_filename = ds($this->cache_root, $handle . '.php');
            \Core\FS::file_create_with_dir($cache_filename, true);
            $created += file_put_contents($cache_filename, $template);
        }

        return $created;
    }

    /**
     * Remove cache for package.
     * --
     * @return boolean
     */
    public function cache_remove()
    {
        if (file_exists($this->cache_root)) {
            return \Core\FS::dir_remove($this->cache_root);
        } else return true;
    }
}