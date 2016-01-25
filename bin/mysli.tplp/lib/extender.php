<?php

namespace mysli\tplp; class extender
{
    const __use = <<<fin
        .{ tplp, parser, exception.extender }
        mysli.toolkit.{ pkg }
        mysli.toolkit.fs.{ fs, file }
        mysli.toolkit.type.{ str }
fin;

    /**
     * Template's root directory.
     * --
     * @var string
     */
    protected $root;

    /**
     * An instance of parser.
     * --
     * @var \mysli\tplp\parser
     */
    protected $parser;

    /**
     * Construct parser.
     * --
     * @param string $root Template root directory.
     * --
     * @throws mysli\tplp\exception\extender 10 No such directory.
     */
    function __construct($root)
    {
        if (!dir::exists($root))
        {
            throw new exception\extender("No such directory: `{$root}`.", 10);
        }

        $this->root = $root;
        $this->parser = new parser();
    }

    /**
     * Process file. This will handle all imports, extends, modules, etc...
     * --
     * @param string $file
     * @param string $root
     *        If different than default. This will load original file
     *        from selected root, but includes from `$this->rooot`.
     * --
     * @return string
     */
    function process($file, $root=null)
    {
        $root = $root ? $root : $this->root;
        $template = $this->load($file, $root);

        $template = $this->extend($template);

        $namespace = $this->make_namespace($file, $root);
        list($uses, $template) = $this->find_uses($template);

        $header = $this->make_header($namespace, $uses);

        return $header.$template;
    }

    /**
     * Load required file from FS.
     * This will process file if not processed already!
     * It will look into following locations:
     * {$root}/{$file}.php
     * {$root}/dist~/{$file}.php
     * {$root}/{$file}.tpl.php
     * {$root}/dist~/{$file}.tpl.php
     * {$tmp}/{$hash}.tpl.php
     * {$root}/{$file}.tpl.html <--- Will be processed and save to {$tmp}
     * --
     * @param string $file
     * @param string $root
     * --
     * @throws mysli\tplp\exception\extender 10 No such file.
     * --
     * @return string
     */
    protected function load($file, $root=null)
    {
        $root = $root ? $root : $this->root;

        $locations = [
            "{$root}/{$file}.php",
            "{$root}/dist~/{$file}.php",
            "{$root}/{$file}.tpl.php",
            "{$root}/dist~/{$file}.tpl.php",
            tplp::tmp_filename($file, $root),
            "{$root}/{$file}.tpl.html",
        ];

        foreach ($locations as $path)
        {
            if (file::exists($path))
            {
                $template = file::read($path);

                // Process & write to tmp directory...
                if (substr($path, -9) === '.tpl.html')
                {
                    $template = $this->parser->process($template);
                    // file::write(tplp::tmp_filename($file, $root), $template);
                }

                return $template;
            }
        }

        throw new exception\extender("No such file: `{$file}`.", 10);
    }

    /**
     * Produce namespace from path and filename.
     * --
     * @param string $file
     * @param string $root
     * --
     * @return string
     */
    protected function make_namespace($file, $root)
    {
        $package = pkg::by_path($root);
        $package = str_replace('.', '\\', $package);
        $package = $package ? $package.'\\' : '';

        $class = str::clean($file, '<[^a-z0-9_\/]+>');
        $class = str_replace('/', '\\', $class);

        return  "tplp\\template\\{$package}{$class}";
    }

    /**
     * Make file header (add namespace and use statements).
     * --
     * @param string $namespace
     * @param array  $uses
     * --
     * @return string
     */
    protected function make_header($namespace, array $uses)
    {
        // Process uses
        $use = [];

        foreach ($uses as $usear)
        {
            $use[] = $usear['statement'];
        }

        // Define header
        $header = [];
        $header[] = "<?php";

        // Add NAMESPACE
        if ($namespace)
        {
            $header[] = "namespace {$namespace};";
        }

        // Add USE\
        if (!empty($use))
        {
            $header = array_merge($header, $use);
        }

        $header[] = "?>";

        if (count($header) > 2)
        {
            return implode("\n", $header);
        }
        else
        {
            return '';
        }
    }

    /**
     * Resolve extend statements.
     * --
     * @param string $template
     * --
     * @return string
     */
    protected function extend($template)
    {
        /*
        ::import [module] from [do ... ::/import]
         */
        $template = preg_replace_callback(
            '/^[ \t]*?'.
            '::import (?<id>[a-z0-9\-_\.\/]+)'.
            '(?: from (?<from>[a-z0-9\-_\.\/]+))?'.
            '(?: do(?<do>.*?)::\/import)?$/ms',
            function ($match)
            {
                if (isset($match['from']))
                {
                    $module = $match['id'];
                    $file = $match['from'];
                }
                else
                {
                    $module = null;
                    $file = $match['id'];
                }

                // Extract set...
                if (isset($match['do']))
                {
                    $set = $this->extract_set($match['do']);
                }
                else
                {
                    $set = [];
                }

                // Load file
                $template = $this->load($file);

                // Extract module
                if ($module)
                    $template = $this->extract_module($template, $module);

                // Process
                $template = $this->extend($template);

                // Resolve prints and return
                if (!empty($set))
                {
                    return $this->resolve_print($template, $set);
                }
                else
                {
                    return $template;
                }
            },
            $template
        );

        /*
        ::extend file set region [do ... ::/extend]
         */
        $extends = [];
        $template = preg_replace_callback(
            '/^[ \t]*?'.
            '::extend (?<file>[a-z0-9\-_\.\/]+)'.
            '(?: set (?<set>[a-z0-9\-_]+))'.
            '(?: do(?<do>.*?)::\/extend)?$/ms',
            function ($match) use (&$extends)
            {
                $extends[] = $match;
                return '';
            },
            $template
        );

        // If no matches, return right now...
        if (empty($extends))
        {
            return $template;
        }

        // Proceed
        foreach ($extends as $extend)
        {
            // Set file
            $file = $extend['file'];

            // Initialize set
            $set = [
                $extend['set'] => trim(rtrim($template), "\n")
            ];

            // Extract sets...
            if (isset($extend['do']))
                $set = array_merge($this->extract_set($extend['do']), $set);

            // Load master file...
            $master = $this->load($file);
            $master = $this->extend($master);

            // Resolve print and return
            $template = $this->resolve_print($master, $set);
        }

        return $template;
    }

    /**
     * Extract set chunks.
     * --
     * @param  string $template
     * --
     * @return array
     */
    protected function extract_set($template)
    {
        $set = [];
        $r = preg_match_all(
            '/^[ \t]*?::set (?<name>[a-z0-9\-_]+)(?<contents>.*?)::\/set$/ms',
            $template,
            $matches,
            PREG_SET_ORDER
        );

        if ($r)
        {
            foreach ($matches as $match)
            {
                $set[$match['name']] = trim(rtrim($match['contents']), "\n");
            }
        }

        return $set;
    }

    /**
     * Resolve print regions...
     * --
     * @param  string $template
     * @param  array  $set
     * --
     * @return string
     */
    protected function resolve_print($template, array $set)
    {
        return
        preg_replace_callback(
            '/^[ \t]*?::print ([a-z0-9\-_]+)$/m',
            function ($match) use ($set)
            {
                if (isset($set[$match[1]]))
                    return $set[$match[1]];
            },
            $template
        );
    }

    /**
     * Find module in a template.
     * --
     * @param  string $template
     * @param  string $module
     * --
     * @throws mysli\tplp\exception\extender Module not found.
     * --
     * @return string
     */
    protected function extract_module($template, $module)
    {
        $search = preg_match(
            '/^::module '.preg_quote($module, '/').'$\s(.*?)\s^::\/module$/ms',
            $template,
            $match
        );

        if ($search)
        {
            return $match[1];
        }
        else
        {
            throw new exception\extender("Module `{$module}` not found.", 10);
        }
    }

    /**
     * Find `::use` statements.
     * --
     * @param string $template
     * --
     * @throws mysli\tplp\exception\extender 10 Name is already previously declared.
     * --
     * @return array [ uses, template ]
     */
    protected function find_uses($template)
    {
        // Find ::use vendor.package( -> name)?
        $uses = [];

        $template = preg_replace_callback(
        '/\R?^[ \t]*?::use ([a-z0-9\_\.]+)(?: \-\> ([a-z0-9_]+))?$\R?/m',
        function ($match) use (&$uses)
        {
            $use = $match[1];

            if (!isset($match[2]))
            {
                $as = substr($use, strrpos($use, '.')+1);
            }
            else
            {
                $as = $match[2];
            }

            if (isset($uses[$as]))
            {
                if ($uses[$as]['use'] !== $use)
                {
                    throw new exception\extender(
                        "Cannot use `{$use}` as `{$as}` because the name ".
                        "is already previously declared.", 10
                    );
                }
            }
            else
            {
                $uses[$as] = [
                    'statement' => "use ".str_replace('.', '\\', $use)."\\__tplp as {$as};",
                    'use'       => $use
                ];
            }

            // Replace with empty
            return;

        }, $template);

        return [ $uses, $template ];
    }
}
