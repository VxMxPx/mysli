<?php

namespace mysli\tplp; class template
{
    const __use = '
        .{
            tplp
            parser
            exception.template
        }
        mysli.toolkit.{
            fs.fs   -> fs
            fs.dir  -> dir
            fs.file -> file
        }
    ';

    /**
     * Full absolute path to the templates root, for this instance.
     * --
     * @var string
     */
    protected$root;

    /**
     * Translator instance.
     * --
     * @var mixed
     */
    protected$translator;

    /**
     * Variables set for this instance.
     * --
     * @var array
     */
    protected$variables = [];

    /**
     * Replaced files.
     * --
     * @var array
     */
    protected$replace = [];

    /**
     * Instance of parser.
     * --
     * @var mysli\tplp\parser
     */
    protected $parser;

    /**
     * Instance of template.
     * --
     * @param string $root Template's root directory.
     */
    function __construct($root)
    {
        $this->root = $root;

        if (dir::exists(fs::ds($root, 'dist~')))
            $root = "{$root}/dist~";

        $this->parser = new parser($root);
    }

    /**
     * Set translator service for template.
     * --
     * @param object $translator
     */
    function set_translator($translator)
    {
        $this->translator = $translator;

        $this->set_function(
            'translator_service',
            function ()
            {
                return call_user_func_array(
                    [$this->translator, 'translate'], func_get_args()
                );
            }
        );
    }

    /**
     * Set local function for template.
     * --
     * @param string   $name
     * @param callable $call
     */
    function set_function($name, $call)
    {
        $this->variables["tplp_func_{$name}"] = $call;
    }

    /**
     * Unset local function by name.
     * --
     * @param string $name
     */
    function unset_function($name)
    {
        if (isset($this->variables["tplp_func_{$name}"]))
            unset($this->variables["tplp_func_{$name}"]);
    }

    /**
     * Set a local variable.
     * --
     * @param string $name
     * @param mixed  $value
     * --
     * @throws mysli\tplp\exception\template
     *         10 Invalid variable name, a variable cannot start with `tplp_`.
     */
    function set_variable($name, $value)
    {
        if (substr($name, 0, 5) === 'tplp_')
        {
            throw new exception\template(
                "Invalid variable name `{$name}`, ".
                "a variable cannot start with: `tplp_`", 10
            );
        }

        $this->variables[$name] = $value;
    }

    /**
     * Unset a local variable.
     * --
     * @param string $name
     */
    function unset_variable($name)
    {
        if (isset($this->variables[$name]))
            unset($this->variables[$name]);
    }

    /**
     * Replace a local file with an external one, or set file which
     * internally doesn't exists.
     *
     * The behavior of this method is exactly the same, as if you'd copy an
     * external file to the current root. The difference is, this method will not
     * actually copy a file.
     *
     * If source file will have any includes,
     * those will be made from the current root.
     * --
     * @param string $file
     *        A file which is bring replaced (with extension!!).
     *
     * @param mixed $with
     *        Array:
     *            Provide an array in following format:
     *            [ string $root, string $filename ]
     *
     *            $root: A root directory from which file is being loaded.
     *            $filename: Actual filename of file being included.
     *
     *        String:
     *            Provide actual template, rather than file. This will not seek
     *            for file, but rather just use the provided template.
     */
    function replace($file, $with)
    {
        $this->parser->replace($file, $with);
        $this->replace[$file] = $with;
    }

    /**
     * Render template.
     * --
     * @param string $file
     * @param array  $variables
     * --
     * @return string
     */
    function render($file, array $variables=[])
    {
        // Find cached file / extend / parse file...
        list($loaded, $file) = $this->get_file($file);

        // Assign variables...
        $variables = array_merge($this->variables, $variables);

        foreach($variables as $_tplpvar => $_tplpval)
        {
            $$_tplpvar = $_tplpval;
        }

        ob_start();
        if ($loaded)
            eval($file);
        else
            include($file);
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Check weather particular template SOURCE file exists.
     * This will omit temporary folder, dist~
     * and replaced files (@see static::replace()).
     * --
     * @param string $file
     * --
     * @return boolean
     */
    function has($file)
    {
        return
        file::exists("{$this->root}/{$file}.php") ||
        file::exists("{$this->root}/{$file}.tpl.php") ||
        file::exists("{$this->root}/{$file}.tpl.html");
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Acquire file, or create it if it doesn't exists.
     * --
     * @param string $file
     * --
     * @throws mysli\tplp\exception\template 10 No such file...
     * @throws mysli\tplp\exception\template 20 Parsing failed...
     * --
     * @return array [ boolean $loaded, string $file ]
     */
    protected function get_file($file)
    {
        if (false !== ($mfile = $this->locate_file($file, 'php')))
        {
            return $mfile;
        }
        else
        {
            foreach (['tpl.php', 'tpl.html'] as $type)
            {
                if (false === ($mfile = $this->locate_file($file, $type)))
                    continue;

                list($loaded, $template) = $mfile;

                if ($loaded)
                {
                    if ($type === 'tpl.php')
                        $action = 'extend';
                    else
                        $action = 'template';
                }
                else
                {
                    $template = basename($template);
                    $action = 'file';
                }

                // Parse // extend...
                try
                {
                    $processed = $this->parser->{$action}($template);
                }
                catch (\Exception $e)
                {
                    throw new exception\template(
                        "Parsing of file `{$file}` failed with message: ".
                        $e->getMessage(), 20
                    );
                }

                // Save file to temporary folder
                $tempfilename = tplp::tmpname($file, $this->root);
                file::write(fs::tmppath('tplp', $tempfilename), $processed);
                return [ false, fs::tmppath('tplp', $tempfilename) ];
            }
        }

        // No file found, oops...
        throw new exception\template("No such file: `{$file}`.", 10);
    }

    /**
     * Locate particular file, and return a full-file path.
     * Primary query target will be replaced files (@see static::replace()).
     * --
     * @param string $file
     * @param string $type php|tpl.php|tpl.html
     * @param string $root If not provided `$this->root` will be used.
     * --
     * @return mixed
     *         array [ boolean $loaded, string $file ]
     *         boolean false If not found
     */
    protected function locate_file($file, $type, $root=null)
    {
        // Define root if not send in
        if (!$root)
            $root = $this->root;

        // Insert replace to be checked
        if (isset($this->replace["{$file}.{$type}"]))
        {
            $template = $this->replace["{$file}.{$type}"];
            if (is_string($template))
                return [ true, $template ];
            else
                return [ false, implode('/', $template) ];
        }

        // Temporary only when looking for PHP
        if ($type === 'php')
            $temporariy = fs::tmppath('tplp', tplp::tmpname($file, $root));

        $dist = "{$root}/dist~/{$file}.{$type}";
        $source = "{$root}/{$file}.{$type}";

        // Check paths
        if ($type === 'php' && file::exists($temporariy))
            return [ false, $temporariy ];
        elseif (file::exists($dist))
            return [ false, $dist ];
        elseif (file::exists($source))
            return [ false, $source ];
        else
            return false;
    }
}
