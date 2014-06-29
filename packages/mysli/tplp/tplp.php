<?php

namespace Mysli\Tplp;

class Tplp
{
    use \Mysli\Tplp\ExtData;

    /**
     * Registry is an array of globally registered functions and variables.
     * --
     * @var array
     */
    static private $registry = [
        'functions' => [],
        'variables' => []
    ];

    /**
     * In case this is first instance, the event will be triggered.
     * --
     * @var boolean
     */
    static private $f_instantiated = false;

    /**
     * Collection of instantiated classes, used for costume function calls in
     * template(s).
     * --
     * @var array
     */
    static private $objects  = [];

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
     * Translator object, used in templates
     * @var object ~i18n
     */
    private $translator = null;

    /**
     * Collection of user defined variables and functions.
     */
    private $functions = [];
    private $variables = [];


    /**
     * Instance of Tplp
     * --
     * @param array  $pkgm_trace
     * @param object $event       mysli/event
     */
    public function __construct(\Mysli\Pkgm\Trace $trace, \Mysli\Event\Event $event)
    {
        if (!self::$f_instantiated) {
            self::$f_instantiated = true;
            $event->trigger('mysli/tplp/tplp:instantiated', [$this]);
            ini_set('pcre.backtrack_limit', '23001337');
            ini_set('pcre.recursion_limit', '23001337');
        }

        $this->package = $trace->get_last();
        // $this->package = array_pop(self::$pkgm_trace)[0];
        $this->cache_dir = datpath('mysli.tplp', str_replace('/', '.', $this->package));

        $this->functions = array_merge($this->functions, self::$registry['functions']);
        $this->variables = array_merge($this->variables, self::$registry['variables']);
    }

    /**
     * Add globally available function...
     * --
     * @param  string   $name
     * @param  callable $function
     * --
     * @return null
     */
    public function register_function($name, $function)
    {
        try {
            $this->set_function($name, $function);
            self::$registry['functions']['tplp_func_' . $name] = $function;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove globally available function...
     * --
     * @param  string $name
     * --
     * @return null
     */
    public function unregister_function($name)
    {
        unset(self::$registry['functions']['tplp_func_' . $name]);
        unset($this->functions['tplp_func_' . $name]);
    }

    /**
     * Add globally available variable...
     * --
     * @param  string $name
     * @param  mixed  $value
     * --
     * @return null
     */
    public function register_variable($name, $value)
    {
        try {
            $this->set_variable($name, $value);
            self::$registry['variables'][$name] = $value;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Remove globally available variable...
     * --
     * @param  string $name
     * --
     * @return null
     */
    public function unregister_variable($name)
    {
        unset(self::$registry['variables'][$name]);
        unset($this->variables[$name]);
    }

    /**
     * Set translator!
     * --
     * @param object $translator
     * --
     * @return null
     */
    public function set_translator($translator)
    {
        $this->translator = $translator;
    }

    /**
     * Get template (object) by name.
     * --
     * @param  string $name
     * --
     * @throws \Core\NotFoundException If template couln't be found.
     * --
     * @return object \Mysli\Tplp\Template
     */
    public function template($name)
    {
        $filename = ds($this->cache_dir, $name . '.php');

        if (!file_exists($filename)) {
            throw new \Core\NotFoundException(
                "Template not found: `{$name}` in `{$filename}`.", 1
            );
        }

        return new \Mysli\Tplp\Template(
            $filename,
            $this->translator,
            $this->variables,
            $this->functions
        );
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
    public function create_cache($folder = 'templates')
    {
        // Check if templates dir exists...
        $templates_dir = pkgpath($this->package, $folder);

        if (!is_dir($templates_dir)) {
            throw new \Core\NotFoundException(
                "Cannot find templates directory: '{$templates_dir}'.", 1
            );
        }

        $templates = $this->find_templates($templates_dir);

        // Create cache directory if not there already
        if (!file_exists($this->cache_dir)) {
            \Core\FS::dir_create($this->cache_dir);
        }

        // Parse all templates...
        foreach ($templates as $handle => &$template) {
            $parser = new \Mysli\Tplp\Parser(
                \Core\Str::to_unix_line_endings(
                    file_get_contents(
                        ds($templates_dir, $handle . '.tplm.html')
                    )
                )
            );
            $template = $parser->parse();
        }

        // Resolve templates' inner dependencies (::use <file>)
        $resover = new \Mysli\Tplp\InclusionsResolver($templates);
        $templates = $resover->resolve();

        // Save files
        $created = 0;
        foreach ($templates as $handle => $template) {
            $cache_filename = ds($this->cache_dir, $handle . '.php');
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
    public function remove_cache()
    {
        if (file_exists($this->cache_dir)) {
            return \Core\FS::dir_remove($this->cache_dir);
        } else return true;
    }


    /**
     * Get template content, standardize line endings.
     * --
     * @param  string $filename
     * --
     * @return string
     */
    private function get_template_content($filename)
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
    private function find_templates($root, $sub = null)
    {
        $templates = [];

        $files = scandir(ds($root, $sub));
        $files = array_diff($files, ['.', '..']);

        foreach ($files as $file) {

            if (is_dir(ds($root, $sub, $file))) {
                $templates = array_merge(
                    $templates,
                    $this->find_templates($root, ds($sub, $file))
                );
            }

            if (substr($file, -10) !== '.tplm.html') {
                continue;
            }

            $handler = trim(ds($sub, substr($file, 0, -10)), '/');
            $templates[$handler] = $this->get_template_content(ds($root, $sub, $file));
        }

        return $templates;
    }
}
