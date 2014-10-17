<?php

namespace mysli\util\tplp;

__use(__namespace__, [
    'mysli/framework' => [
        'fs/{fs/file}',
        'exception' => 'framework/exception/%s'
    ]
]);

class template {

    private $package;
    private $translator;
    private $variables;

    /**
     * @param string $package
     */
    function __construct($package) {
        $this->package = $package;
    }
    /**
     * Set translator for template
     * @param object $translator
     */
    function set_translator($translator) {
        $this->translator;
        $this->set_function('translator_service', function () {
            return call_user_func_array(
                        [$this->translator, 'translate'], func_get_args());
        });
    }
    /**
     * Set local function for template
     * @param string   $name
     * @param callable $call
     */
    function set_function($name, $call) {
        $this->variables["tplp_$name"] = $call;
    }
    /**
     * Unset local function by name
     * @param  string $name
     */
    function unset_function($name) {
        if (isset($this->variables["tplp_{$name}"])) {
            unset($this->variables["tplp_{$name}"]);
        }
    }
    /**
     * Set a local variable
     * @param string $name
     * @param mixed  $value
     */
    function set_variable($name, $value) {
        if (substr($name, 0, 5) === 'tplp_') {
            throw new framework\exception\argument(
                "Invalid variable name `{$name}`, ".
                "a variable cannot start with: `tplp_`", 1);
        }
        $this->variables[$name] = $value;
    }
    /**
     * Unset a local variable
     * @param  string $name
     */
    function unset_variable($name) {
        if (isset($this->variables[$name])) {
            unset($this->variables[$name]);
        }
    }
    /**
     * Render template
     * @param  string $file
     * @param  array  $variables
     * @return string
     */
    function render($file, array $variables=[]) {

        $file = $this->create_and_get_file($file);

        // Assign variables...
        $variables = array_merge($this->variables, $variables);
        foreach($variables as $_tplpvar => $_tplpval) {
            $_tplpvar = $_tplpval;
        }

        ob_start();
            include($file);
            $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }

    /**
     * Get parsed template absolute path, if exists,
     * otherwise parse it and then return the path
     * @param  string $file
     * @return string
     */
    private function create_and_get_file($file) {
        $cache_file = fs::datpath('mysli/tplp/cache/' .
                                  str_replace('/', '.', $this->package),
                                  $file.'.php');

        if (!file::exists($cache_file)) {
            $tplp_folder = fs::datpath($this->package, 'tplp');
            $tplp_path = fs::pkgpath($this->package, "tplp/{$file}.tplp");
            if (!file::exists($tplp_path)) {
                throw new framework\exception\not_found(
                    "File `{$file}.tplp` not found in `" .
                    $tplp_folder . '`', 1);
            }
            $parsed = parser::file("{$file}.tplp", $tplp_folder);
            file::create_recursive($cache_file, true);
            file::write($cache_file, $parser);
        }

        return $cache_file;
    }
}
