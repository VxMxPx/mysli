<?php

namespace mysli\tplp; class template
{
    const __use = <<<fin
        .{ tplp, parser, exception.template }
        mysli.toolkit.fs.{ fs, dir, file }
fin;

    /**
     * Full absolute path to the templates root, for this instance.
     * --
     * @var string
     */
    protected $root;

    /**
     * Translator instance.
     * --
     * @var mixed
     */
    protected $translator;

    /**
     * Variables set for this instance.
     * --
     * @var array
     */
    protected $variables = [];

    /**
     * Replaced files.
     * --
     * @var array
     */
    protected $replace = [];

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
        $this->parser = new parser();
        $this->extender = new extender($root);
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
     * Render template.
     * --
     * @param mixed  $file
     *        String: filename to render, e.g.: `default_template`.
     *        Array:  [vendor.package, filename] to render partial from another
     *        template (while using includes from current root).
     *
     * @param array  $variables
     *
     * @param boolean $reload
     *        Force template re-rendering, even if cached version exists.
     * --
     * @return string
     */
    function render($file, array $variables=[], $reload=false)
    {
        if (is_array($file))
        {
            list($package, $file) = $file;
            $root = tplp::get_path($package);
        }
        else
        {
            $root = $this->root;
        }

        $loc_filename = fs::ds($root, $file.'.composed');
        $tmp_filename = tplp::tmp_filename($file, $root).'.composed';

        if (file::exists($loc_filename) && !$reload)
        {
            $filename = $loc_filename;
        }
        else
        {
            if (!file::exists($tmp_filename) || $reload)
            {
                $template = $this->extender->process($file, $root);
                file::write($tmp_filename, $template);
            }
            $filename = $tmp_filename;
        }

        // Assign variables...
        $variables = array_merge($this->variables, $variables);

        foreach($variables as $_tplpvar => $_tplpval)
        {
            $$_tplpvar = $_tplpval;
        }

        ob_start();
        include($filename);
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * See if template exists.
     * --
     * @param string $file
     * @param string $root
     * --
     * @return boolean
     */
    function has($file, $root=null)
    {
        $root = !$root ? $this->root : $root;

        $locations = [
            "{$root}/{$file}.php",
            "{$root}/dist~/{$file}.php",
            "{$root}/{$file}.tpl.php",
            "{$root}/dist~/{$file}.tpl.php",
            tplp::tmp_filename($file, $root),
            "{$root}/{$file}.tpl.html",
        ];

        foreach ($locations as $loc)
        {
            if (file::exists($loc))
            {
                return true;
            }
        }

        return false;
    }
}
