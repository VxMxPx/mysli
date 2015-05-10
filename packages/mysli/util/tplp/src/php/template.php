<?php

namespace mysli\util\tplp;

__use(__namespace__, '
    ./tplp
    mysli.framework.fs/fs,file
    mysli.framework.exception/* -> framework\exception\*
');

class template
{
    private $package;
    private $translator;
    private $variables = [];

    private $source;
    private $dest;

    /**
     * @param string $package
     */
    function __construct($package)
    {
        $this->package = $package;
        list($this->source, $this->dest) = tplp::get_paths($package);
    }
    /**
     * Set translator for template
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
     * Set local function for template
     * @param string   $name
     * @param callable $call
     */
    function set_function($name, $call)
    {
        $this->variables["tplp_func_{$name}"] = $call;
    }
    /**
     * Unset local function by name
     * @param  string $name
     */
    function unset_function($name)
    {
        if (isset($this->variables["tplp_func_{$name}"]))
        {
            unset($this->variables["tplp_func_{$name}"]);
        }
    }
    /**
     * Set a local variable
     * @param string $name
     * @param mixed  $value
     */
    function set_variable($name, $value)
    {
        if (substr($name, 0, 5) === 'tplp_')
        {
            throw new framework\exception\argument(
                "Invalid variable name `{$name}`, ".
                "a variable cannot start with: `tplp_`", 1
            );
        }

        $this->variables[$name] = $value;
    }
    /**
     * Unset a local variable
     * @param  string $name
     */
    function unset_variable($name)
    {
        if (isset($this->variables[$name]))
        {
            unset($this->variables[$name]);
        }
    }
    /**
     * Render template
     * @param  string $file
     * @param  array  $variables
     * @return string
     */
    function render($file, array $variables=[])
    {
        $file = fs::ds($this->dest, $file.'.php');

        if (!file::exists($file))
        {
            throw new framework\exception\not_found(
                "Template file not found: `{$file}`");
        }

        // Assign variables...
        $variables = array_merge($this->variables, $variables);

        foreach($variables as $_tplpvar => $_tplpval)
        {
            $$_tplpvar = $_tplpval;
        }

        ob_start();
        include($file);
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Check weather particuar template file exists.
     * @param  string  $file
     * @return boolean
     */
    function has($file)
    {
        $source_file = fs::ds($this->dest, $file.'.php');
        return file::exists($source_file);
    }
}
