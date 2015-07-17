<?php

namespace mysli\toolkit\cli; class param
{
    /**
     * List of allowed types, accepted by self::type
     */
    const types = '|string|boolean|integer|float|array|';

    /**
     * List of options for this parameter.
     * --
     * @var array
     */
    private $options = [
        'id'         => null,
        'name'       => null,
        'short'      => null,
        'long'       => null,
        'positional' => null,
        'type'       => 'string',
        'def'        => null,
        'help'       => null,
        'required'   => false,
        'exclude'    => [],
        'invert'     => false
    ];

    /**
     * Weather this field received value.
     * --
     * @var boolean
     */
    private $is_set = false;

    /**
     * Actual value for this parameter.
     * --
     * @var mixed
     */
    private $value = null;

    /**
     * Construct a new parameter with particular ID.
     * --
     * @param string $id
     *        An unique id (position || long || short).
     *        Example: long/short --- long/s (--long -s) or only long.
     *        If you desire this to be positional parameter omit dashes and use:
     *        POSITIONAL, for example: USERNAME or PACKAGE, ...
     */
    function __construct($id)
    {
        $this->options['id'] = $id;

        /*
        Check if both short and long variations were passed in.
        For example: --long/-s
         */
        if (strpos($id, '/'))
        {
            $segments = explode('/', $id);

            if (substr($segments[0], 0, 2) === '--')
            {
                $this->options['long'] = substr($segments[0], 2);
                $this->options['short'] = substr($segments[1], 1);
            }
            else
            {
                $this->options['long'] = substr($segments[1], 2);
                $this->options['short'] = substr($segments[0], 1);
            }
        }
        else
        {
            // Either only long, short or positional
            if (substr($id, 0, 2) === '--')
                $this->options['long'] = substr($id, 2);
            elseif (substr($id, 0, 1) === '-')
                $this->options['short'] = substr($id, 1);
            else
                $this->options['positional'] = strtolower($id);
        }

        /*
        Set name from long/positional/short
         */
        if ($this->options['long'])
            $this->options['name'] = $this->options['long'];
        else if ($this->options['positional'])
            $this->options['name'] = $this->options['positional'];
        else
            $this->options['name'] = $this->options['short'];
    }

    /**
     * Get particulad option for list of options.
     * --
     * @param string $option
     * --
     * @throws \Exception 10 No such option.
     * --
     * @return mixed
     */
    function option($option)
    {
        if (array_key_exists($option, $this->options))
            return $this->options[$option];
        else
            throw new \Exception("No such options: `{$option}`", 10);

    }

    /**
     * Set, process, validate value for this parameter.
     * --
     * @param mixed $value String or true in case of boolean.
     * --
     * @throws \Exception 10 Parameter exclude another parameter.
     * @throws \Exception 20 Numeric value is expected.
     * @throws \Exception 21 Numeric value is expected.
     * @throws \Exception 22 Invalid parameter type.
     */
    function set_value($value)
    {
        // Check if any of excludes has value, and throw exception if does
        foreach ($this->option('exclude') as $exclude)
        {
            if ($exclude->is_set())
                throw new \Exception(
                    sprintf(
                        "Parameter `%s` excludes parameter `%s`",
                        $this->option('name'),
                        $exclude->option('name')
                    ),
                    10
                );
        }

        switch ($this->option('type')) {
            case 'string':
                $this->value = (string) $value;
                break;

            case 'boolean':
                if ($value === true || strtolower($value) === 'true')
                    $this->value = true;
                else
                    $this->value = false;
                break;

            case 'integer':
                if (!is_numeric($value))
                    throw new \Exception("Numeric value was expected!", 20);
                else
                    $this->value = (int) $value;
                break;

            case 'float':
                if (!is_numeric($value))
                    throw new \Exception("Numeric value was expected!", 21);
                else
                    $this->value = (float) $value;
                break;

            case 'array':
                $this->value = explode(',', $value);
                break;

            default:
                throw new \Exception(
                    "Invalid parameter type: `".$this->option('type')."`.", 22
                );
        }

        // Set is_set
        $this->is_set = true;

        // Does value needs to be inverted?
        if ($this->option('invert'))
        {
            switch ($this->option('type')) {
                case 'string':
                    $this->value = strrev($this->value);
                    break;

                case 'boolean':
                    $this->value = !$this->value;
                    break;

                case 'integer':
                case 'float':
                    $this->value = $this->value*-1;
                    break;

                case 'array':
                    $this->value = array_reverse($this->value);
                    break;
            }
        }
    }

    /**
     * Ger value for this parameter.
     * --
     * @return mixed
     */
    function get_value()
    {
        if (!$this->is_set)
        {
            return $this->option('def');
        }
        else
        {
            return $this->value;
        }
    }

    /**
     * Check weather this parameter value was set.
     * --
     * @return boolean
     */
    function is_set()
    {
        return $this->is_set;
    }

    /**
     * Set this parameter's type. Default is: `string`
     * --
     * @param string $type
     *        Allowed types are:
     *        - string  foo => "foo"
     *        - boolean presence of argument means true, for example,
     *                  if parameter's short version is -b, then if -b is
     *                  passed value will be true.
     *        - integer 12 => 12
     *        - float   11.2 => 11.2
     *        - array   one,two => ['one', 'two']
     * --
     * @throws \Exception 10 Invalid type.
     * --
     * @return self
     */
    function type($type)
    {
        if (strpos(self::types, "|{$type}|") === false)
            throw new \Exception(
                "Invalid type: `{$type}`, allowed types are: `".self::type."`",
                10
            );

        $this->options['type'] = $type;

        return $this;
    }

    /**
     * Default value, if no argument was provided.
     * --
     * @param mixed $default
     * --
     * @return self
     */
    function def($default)
    {
        $this->options['def'] = $default;
        return $this;
    }

    /**
     * Set help text for this parameter.
     * --
     * @param  string $help
     * --
     * @return self
     */
    function help($help)
    {
        $this->options['help'] = (string) $help;
        return $this;
    }

    /**
     * Weather parameter is required. If this is set to true,
     * and argument is missing, this field will be invalidated.
     * --
     * @param boolean $required
     * --
     * @return self
     */
    function required($required)
    {
        $this->options['required'] = !!$required;
        return $this;
    }

    /**
     * Which argument(s) cannot be passed in combination with this one.
     * --
     * @param mixed $...
     *        Many instance of self, false an array with many instance of self.
     * --
     * @throws \Exception 10 All arguments needs to be an instance of `param`.
     * --
     * @return self
     */
    function exclude()
    {
        $args = func_get_args();

        /*
        If first argument is false, then it's safe to assume it was intended
        for this option to be turned off.
         */
        if (!$args[0])
            $this->options['exclude'] = [];

        /*
        If first parameter is array,
        that means excludes were send in as an array rather than as arguments.
        This is common when `create_parameter` option is used.
         */
        if (is_array($args[0]))
            $args = $args[0];

        /*
        Go through list of provided parameter
        and make sure each of them is of `param` type.
         */
        foreach ($args as $param)
        {
            if (!is_object($param) || !is_a($param, '\\mysli\\toolkit\\cli\\param'))
            {
                throw new \Exception(
                    "All arguments needs to be an instance of `param`, got: `".
                    gettype($param)."`.", 10
                );
            }
            else
            {
                $id = $param->option('id');

                if (!isset($this->options['exclude'][$id]))
                {
                    // Add parameter to exclude list,
                    // and self to parameter's exclude list.
                    $this->options['exclude'][$id] = $param;
                    $param->exclude($this);
                }
            }
        }

        return $this;
    }

    /**
     * Invert value.
     * The action depends on type:
     * float/integer  12 => -12
     *       boolean  true => false
     *         array  [white, blue, red] => [red, blue, white]
     *        string  red => der
     * This option will probably be most useful for booleans, but for the
     * sake of consistency it applies to other types too.
     * --
     * @param boolean $invert
     * --
     * @return self
     */
    function invert($invert)
    {
        $this->options['invert'] = !!$invert;
        return $this;
    }
}
