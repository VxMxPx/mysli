<?php

namespace Mysli\Tplp;

trait ExtData
{
    /**
     * Define a new function, will be passed to all sub-templates.
     * --
     * @param  string   $name
     * @param  callable $function
     * --
     * @throws \Core\ValueException If function name is not valid: a-zA-Z0-9_ (1)
     * @throws \Core\ValueException If function start with: tplp_ (2)
     * --
     * @return null
     */
    public function function_set($name, $function)
    {
        if (!preg_match('/^[a-z_][a-z0-9_]*?$/i', $name)) {
            throw new \Core\ValueException(
                "Invalid function name: `{$name}`.".
                'Function name can contain only: `a-zA-Z0-9_`.', 1
            );
        }

        if (substr($name, 0, 5) === 'tplp_') {
            throw new \Core\ValueException("Function cannot start with: `tplp_`.", 2);
        }

        $this->functions['tplp_func_' . $name] = $function;
    }

    /**
     * Remove function.
     * --
     * @param  string $name
     * --
     * @return null
     */
    public function function_remove($name)
    {
        unset($this->functions['tplp_func_' . $name]);
    }

    /**
     * Set variables.
     * --
     * @param mixed $name - string|array
     * @param mixed $value
     * --
     * @throws \Core\ValueException If variable name is not valid: a-zA-Z0-9_ (1)
     * @throws \Core\ValueException If Variable start with: tplp_ (2)
     * --
     * @return null
     */
    public function variable_set($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $var => $val) {
                $this->variable_add($var, $val);
            }
            return;
        }

        if (!preg_match('/^[a-z_][a-z0-9_]*?$/i', $name)) {
            throw new \Core\ValueException(
                "Invalid variable name: `{$name}`.".
                'Variable name can contain only: `a-zA-Z0-9_`.', 1
            );
        }

        if (substr($name, 0, 5) === 'tplp_') {
            throw new \Core\ValueException("Variable cannot start with: `tplp_`.", 2);
        }

        $this->variables[$name] = $value;
    }

    /**
     * Remove variable.
     * --
     * @param  string $name
     * --
     * @return null
     */
    public function variable_remove($name)
    {
        unset($this->variables[$name]);
    }

    /**
     * Get variable's value if exists.
     * --
     * @param  string $name
     * --
     * @return mixed  Null if variable not set.
     */
    public function variable_get($name)
    {
        if (isset($this->variables[$name])) {
            return $this->variables[$name];
        }
    }
}
