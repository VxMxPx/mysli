<?php

/**
 * Handle script input parameters.
 */
namespace dot; class param
{
    /**
     * Terminal width.
     * --
     * @var integer
     */
    private $term_width;

    /**
     * List of expected parameters to be handled.
     * --
     * @var array
     */
    private $params = [];

    /**
     * List of values: processed arguments assigned to expected parameters.
     * --
     * @example
     * Param: $param->add('--name', ['type' => 'str'])
     * Input: ./dot script --name Inna
     * Result: $values = array('name' => 'Inna')
     * --
     * @var array
     */
    private $values = [];

    /**
     * List of actual raw arguments from $_SYSTEM[argv], to be processed.
     * ---
     * @var array
     */
    private $arguments = [];

    /**
     * List of messages (warning and errors) to be returned to the user.
     * --
     * @var array
     */
    private $messages = [];

    /**
     * Weather user's provided arguments were valid.
     * --
     * @var boolean
     */
    private $is_valid = false;

    /**
     * Title to be used when help is printed.
     * --
     * @var string
     */
    public $title = null;

    /**
     * Command name, to be used when help is printed.
     * --
     * @var string
     */
    public $command = null;

    /**
     * Description, used when help is printed.
     * --
     * @var string
     */
    public $description = null;

    /**
     * Longer description, printed at the end.
     * --
     * @var string
     */
    public $description_long = null;

    /**
     * Defaults for each new parameter added.
     * Values are:
     *
     * @param string id
     *        An unique id (position || long || short), this options is NOT required.
     *        ID will be automatically created from parameter's name.
     *
     * @param string short
     *        A short (-s) version of parameter, NOT required, it will be
     *        automatically created from name. For example if name is (-s/--long)
     *        this will be set to -s.
     *
     * @param string long
     *        A long (--long) version of parameter.
     *        See explanation of _short_ for details.
     *
     * @param string type
     *        Parameter's type, argument will be converted to the selected type.
     *        Available types:
     *        - str   string  foo  => "foo"
     *        - bool  boolean presence of argument means true, for example,
     *                        if parameter's short version is -b, then if -b is
     *                        passed value be true.
     *        - int   integer 12   => 12
     *        - float float   11.2 => 11.2
     *        - arr   array   one,two => ['one', 'two']
     *
     * @param integer min
     *        Minimum value when integer or float.
     *        Minimum amount of elements when array.
     *
     * @param integer max
     *        Maximum value, when integer or float.
     *        Maximum amount of elements when array.
     *
     * @param mixed default
     *        Default value, if no argument provided.
     *        Default will also be used if argument is present but
     *        has no value assigned, e.g.: --param --param2
     *
     * @param string help
     *        Help text.
     *
     * @param boolean required
     *        Weather parameter is required. If this is set to true,
     *        and argument is missing, `is_valid` will return false, and message
     *        will be added.
     *
     * @param boolean positional
     *        Weather this parameter is positional. This can be automatically
     *        determent from name, if name is MY_POSITIONAL (all upper case, no
     *        dashes (-) for short/long format, then this will be set to true).
     *        An example of positional parameter:
     *        `command -n foo --named foo "I'm positional"`.
     *
     * @param boolean allow_empty
     *        Weather empty values are allowed.
     *        If `default` is not set, then this would result in:
     *        string: '', bool: false, int: 0, float: 0.0, array: []
     *
     * @param array exclude
     *        Which argument(s) cannot be set in combination with this one.
     *
     * @param callable modify
     *        Invoke a particular method, which will be called after all
     *        arguments are parsed. It will be called **only** if validation
     *        passes. Following arguments will be send to the function:
     *        1. mixed $value the value for this parameter
     *        2. array $arguments collection of all arguments
     *        Return modified value! Do NOT omit return, because that will
     *        set value to empty.
     *
     * @param callable validate
     *        //TODO: Only only anonymous functions are accepted, because
     *        // call_user_func does not pass by reference.
     *        Execute a function when argument is being parsed.
     *        Following arguments will be sent to the function:
     *        1. mixed   $value the value for this parameter
     *        2. boolean $is_valid weather validation passed; own validation can
     *                             be implemented at this place, and this value
     *                             can be freely manipulated.
     *        3. array   $messages collection of messages from all parameters.
     *                             in case of costume validation, new messages
     *                             can be pushed in.
     *        Boolean return is expected.
     *
     * @param boolean invert
     *        Invert boolean value.
     *
     * @param boolean ignore
     *        Value of this parameter will be disregarded.
     *        This is used when parameter's value is not needed on list, like
     *        in the case of --help for example.
     * --
     * @var array
     */
    private $defaults = [
        'id'         => null,
        'short'      => null,
        'long'       => null,
        'type'       => 'str',
        'min'        => null,
        'max'        => null,
        'default'    => null,
        'help'       => null,
        'required'   => null,
        'positional' => false,
        'allow_empty'=> false,
        'exclude'    => false,
        'modify'     => false,
        'validate'   => false,
        'invert'     => false,
        'ignore'     => false
    ];

    /**
     * Construct cli/param
     * --
     * @param string $title
     * @param array  $arguments
     */
    function __construct($title=null, array $arguments=null)
    {
        $this->term_width = util::terminal_width() ?: 75;

        $this->arguments = !is_null($arguments)
            ? $arguments
            : array_slice($_SERVER['argv'], 1);

        $this->title = $title;

        $this->add('--help/-h', [
            'type'     => 'bool',
            'help'     => 'Display this help',
            'ignore'   => true,
            'validate' => function ($val, &$is_valid, &$messages) {
                $is_valid = false;
                $messages = [];
                $messages[] = $this->help();
                return false;
            }
        ]);
    }

    /**
     * Add parameter.
     * --
     * @param string $name long/short e.g.: long/s (--long -s) or only long
     * @param array  $options for list of accepted options @see: self::$defaults
     */
    function add($name, array $options = [])
    {
        list($positional, $long, $short) = $this->parse_name_params($name);
        $options = array_merge($this->defaults, $options);
        $options['positional'] = !!$positional;
        $options['long']       = $long;
        $options['short']      = $short;
        $options['id'] = $options['id']
            ?: ($positional
                ?: ($long ?: $short));

        if ($positional && $options['required'] === null)
        {
            $options['required'] = true;
        }

        $this->options_validate($options);
        // null to false
        $options['required'] = !!$options['required'];
        $this->params[$options['id']] = $options;
    }

    /**
     * Return list of messages, which are set after parse()
     * --
     * @return string
     */
    function messages()
    {
        return implode("\n", $this->messages);
    }

    /**
     * Return status (weather execution was valid, all required parameters
     * were set, etc...)
     * --
     * @return boolean
     */
    function is_valid()
    {
        return $this->is_valid;
    }

    /**
     * Get list of values.
     * --
     * @return array
     */
    function values()
    {
        return $this->values;
    }

    /**
     * Return list of currently set parameters.
     * --
     * @return array
     */
    function dump()
    {
        return [
            $this->params,
            $this->values,
            $this->messages,
            $this->is_valid
        ];
    }

    /**
     * Parse all parameters.
     * --
     * @return boolean (is_valid)
     */
    function parse()
    {
        $this->values   = [];
        $this->messages = [];
        $this->is_valid = true;
        $b              = false; // break the loop and return immediately
        $current        = null;

        foreach ($this->arguments as $arg)
        {
            if (is_array($current) && substr($arg, 0, 1) === '-')
            {
                if ($current['type'] === 'arr')
                {
                    $values_c = isset($this->values[$current['id']])
                        ? count($this->values[$current['id']])
                        : 0;

                    if ($current['min'] !== null &&
                        count($values_c) < $current['min'])
                    {
                        $this->invalidate(
                            "Expected at least `{$current['min']}` ".
                            "arguments for `{$current['id']}`."
                        );
                        return false;
                    }

                    $current = null;
                }
                elseif ($current['allow_empty'])
                {
                    if ($current['default'] !== null)
                    {
                        $this->set_value($current['default'], $current);
                    }
                    else
                    {
                        $this->set_value(
                            $this->get_null_value($current['type']),
                            $current
                        );
                    }

                    if ($this->is_valid)
                    {
                        $current = null;
                    }
                    else
                    {
                        return false;
                    }
                }
                else
                {
                    $this->invalidate("Expected value for: {$current['id']}");
                    return false;
                }
            }
            if (is_array($current))
            {
                if ($this->set_value($arg, $current) === false)
                {
                    return false;
                }
                else
                {
                    if ($current['type'] === 'arr')
                    {
                        $has_max = ($current['max'] !== null);
                        if ($has_max)
                        {
                            $has_value = isset($this->values[$current['id']]);
                            $values_c  = $has_value
                                ? count($this->values[$current['id']])
                                : 0;

                            if ($has_value && $values_c >= $current['max'])
                            {
                                $current = null;
                            }
                        }
                    }
                    else
                    {
                        $current = null;
                    }
                }
            }
            elseif (substr($arg, 0, 2) === '--')
            {
                $arg = substr($arg, 2);
                if (!($current = $this->resolve_value($arg, 'long')))
                {
                    return false;
                }
            }
            elseif (substr($arg, 0, 1) === '-')
            {
                $arg = substr($arg, 1);
                if (strlen($arg) > 1)
                {
                    foreach (str_split($arg) as $sarg)
                    {
                        if (!$this->resolve_value($sarg, 'short', false))
                        {
                            return false;
                        }
                    }
                }
                else
                {
                    if (!($current = $this->resolve_value($arg, 'short')))
                    {
                        return false;
                    }
                }
            }
            else
            {
                if ($opt = $this->find($arg, 'positional'))
                {
                    if ($this->set_value($arg, $opt) === false)
                    {
                        return false;
                    }
                    else
                    {
                        $current = null;
                    }
                }
                else
                {
                    $this->invalidate("Unexpected argument: `{$arg}`");
                    return false;
                }
            }
        }

        if (is_array($current))
        {
            if ($current['type'] === 'arr')
            {
                $values_c = isset($this->values[$current['id']])
                    ? count($this->values[$current['id']])
                    : 0;

                if ($current['min'] !== null &&
                    count($values_c) < $current['min'])
                {
                    $this->invalidate(
                        "Expected at least `{$current['min']}` ".
                        "arguments for `{$current['id']}`."
                    );
                    return false;
                }
                $current = null;
            }
            elseif ($current['allow_empty'])
            {
                if ($current['default'] !== null)
                {
                    $this->set_value($current['default'], $current);
                }
                else
                {
                    $this->set_value(
                        $this->get_null_value($current['type']),
                        $current
                    );
                }

                if ($this->is_valid)
                {
                    $current = null;
                }
                else
                {
                    return false;
                }
            }
            else
            {
                $this->invalidate("Expected value for: {$current['id']}");
                return false;
            }
        }

        // if (empty($this->values)) {
        //     $this->is_valid = false;
        //     $this->messages[] = $this->help();
        //     return false;
        // }

        // check for exclude errors and set list of items to be invoked
        $invoke = [];
        foreach ($this->params as $pid => $opt)
        {
            if (array_key_exists($pid, $this->values) && $opt['exclude'])
            {
                $exclude = is_array($opt['exclude'])
                    ? $opt['exclude']
                    : [$opt['exclude']];

                foreach ($exclude as $exc)
                {

                    if (isset($this->values[$exc])) {
                        $this->invalidate(
                            "You cannot use both `{$pid}` and `{$exc}`"
                        );
                        return false;
                    }
                }
            }

            if ($opt['modify'] && isset($this->values[$pid]))
            {
                $invoke[$pid] = $opt['modify'];
            }
        }
        // check weather all required items are set
        foreach ($this->params as $pid => $opt)
        {
            if (!isset($this->values[$pid]) && !$opt['ignore'])
            {
                if ($opt['required'])
                {
                    $this->invalidate("Missing parameter: `{$pid}`");
                    return false;
                }
                else
                {
                    $this->values[$pid] = $opt['default'];
                }
            }
        }
        // check if any parameters to be modified is present
        // we need separate loop for this, as all values needs to be set before
        // so that they can be passed in.
        foreach ($invoke as $pid => $call)
        {
            $this->values[$pid] = call_user_func_array(
                $call, [$this->values[$pid], $this->values]
            );
        }

        return true;
    }

    /**
     * Return full (auto-generated) help.
     * --
     * @return string
     */
    function help()
    {
        $command = $this->command ?: "COMMAND";
        $sargs   = $this->fromat_arguments_short();
        $pargs   = $this->fromat_arguments_detailed('positional');
        $dargs   = $this->fromat_arguments_detailed();

        $description = $this->description
            ? (strlen($this->description) > $this->term_width
                ? substr($this->description, 0, ($this->term_width-3)).'...'
                : $this->description)
            : '';

        return
            ($this->title ? "\n".ui::title($this->title, true)."\n" : '').
            ($this->description ? "{$description}\n" : '').
            "\nUsage: ./dot {$command} {$sargs}\n".
            ($pargs ? "\n{$pargs}\n" : '').
            ($dargs ? "\n".ui::title('Options:', true)."\n{$dargs}\n" : '').
            ($this->description_long
                ? "\n".wordwrap($this->description_long, $this->term_width)."\n"
                : ''
            );
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Set null value for particular type:
     *   str   : ''
     *   int   : 0
     *   float : 0.0
     *   bool  : false
     *   arr   : []
     * --
     * @param string $type
     * --
     * @return mixed
     */
    private function get_null_value($type)
    {
        switch ($type)
        {
            case 'str'  : return '';
            case 'int'  : return 0;
            case 'float': return 0.0;
            case 'bool' : return false;
            case 'arr'  : return [];
            default:      return null;
        }
    }

    /**
     * Find parameter, see if bool is expected, set value (if bool)
     * --
     * @param string  $arg
     * @param string  $type
     * @param boolean $get_current
     * --
     * @return mixed
     */
    private function resolve_value($arg, $type, $get_current=true)
    {
        if ($opt = $this->find($arg, $type))
        {
            if ($opt['type'] === 'bool')
            {
                return $this->set_value($arg, $opt);
            }
            else
            {
                if (!$get_current)
                {
                    $this->invalidate("Expected boolean for: `{$arg}`");
                }
                else
                {
                    return $opt;
                }
            }
        }
        else
        {
            $this->invalidate("Invalid argument: `{$arg}`");
        }

        return true;
    }

    /**
     * Will set particular value to the current values list: helper of parse
     * --
     * @param string $arg
     * @param array  $opt
     * --
     * @return boolean
     */
    private function set_value($arg, array $opt)
    {
        $value = $this->set_type($arg, $opt['type'], $opt['invert']);
        $rt = true;

        if (is_callable($opt['validate']))
        {
            $rt = $opt['validate']($value, $this->is_valid, $this->messages);
        }

        if (!$opt['ignore'])
        {
            if ($opt['type'] === 'arr')
            {
                $this->values[$opt['id']][] = $value;
            }
            else
            {
                if ($opt['max'] !== null &&
                    ($opt['type'] === 'int' || $opt['type'] === 'float') &&
                    $value > $opt['max'])
                {
                    $this->invalidate(
                        "The `{$opt['id']}` is more than the maximum allowed ".
                        "value, can be maximum: `{$opt['max']}`, ".
                        "your value: `{$value}`"
                    );
                }

                if ($opt['min'] !== null &&
                    ($opt['type'] === 'int' || $opt['type'] === 'float') &&
                    $value < $opt['min'])
                {
                    $this->invalidate(
                        "The `{$opt['id']}` is less than the minimum allowed ".
                        "value. Need to be at least `{$opt['min']}`, ".
                        "your value: `{$value}`"
                    );
                }

                $this->values[$opt['id']] = $value;
            }
        }

        return $rt;
    }

    /**
     * Format arguments and print them as string.
     * --
     * @return string
     */
    private function fromat_arguments_short()
    {
        $return = '';
        $count = 0;
        $required = 0;
        $positionals = [];

        foreach ($this->params as $pid => $opt)
        {
            if (!$opt['positional'])
            {
                $count++;
                $required += $opt['required'];
            }
            else
            {
                $positionals[] = $opt['required']
                    ? strtoupper($pid)
                    : '[' . strtoupper($pid) . ']';
            }
        }

        if ($count > 0)
        {
            $return = 'OPTIONS';
        }

        if ($required === 0)
        {
            $return = "[{$return}]";
        }

        if ($count > 1)
        {
            $return .= '...';
        }

        if ($positionals)
        {
            $return .= ' ' . implode(' ', $positionals);
        }

        return $return;
    }

    /**
     * Format arguments and print them as string.
     * --
     * @param string $type null|positional
     * --
     * @return string
     */
    private function fromat_arguments_detailed($type=null)
    {
        $params = [];
        $lkey = 0;
        $ldefault = 0;
        $lmax = $this->term_width - 15;

        foreach ($this->params as $pid => $opt)
        {
            if (($type === 'positional' && !$opt['positional']) ||
                ($type !== 'positional' &&  $opt['positional']))
            {
                continue;
            }

            // set key
            if ($opt['positional'])
            {
                $params[$pid]['key'] = strtoupper($pid);
            }
            else
            {
                if ($opt['long'] && $opt['short'])
                {
                    $params[$pid]['key'] = "-{$opt['short']}, --{$opt['long']}";
                }
                elseif ($opt['long'])
                {
                    $params[$pid]['key'] = "    --{$opt['long']}";
                }
                else
                {
                    $params[$pid]['key'] = "-{$opt['short']}";
                }
            }

            // set default
            if ($opt['default'] !== null)
            {
                if (is_bool($opt['default']))
                {
                    if (!$opt['default'])
                    {
                        $params[$pid]['default'] = '[false]';
                    }
                    else
                    {
                        $params[$pid]['default'] = '[true]';
                    }
                }
                else
                {
                    $default = (string) $opt['default'];

                    if (strlen($default) > 15)
                    {
                        $default = substr($default, 0, 13) . '...';
                    }
                    $params[$pid]['default'] = "[{$default}]";
                }
            }
            else
            {
                $params[$pid]['default'] = '';
            }

            // set help
            $params[$pid]['help'] = $opt['help'];

            // set lengths
            if (strlen($params[$pid]['key']) > $lkey)
            {
                $lkey = strlen($params[$pid]['key']);
            }

            if (strlen($params[$pid]['default']) > $ldefault)
            {
                $ldefault = strlen($params[$pid]['default']);
            }
        }

        // full length of key + default + spaces:
        // --key [default]
        $lfull = ($lkey + 2) + ($ldefault ? $ldefault + 3 : 2);

        // if too long, new line
        if ($lfull > $lmax)
        {
            $lfull = 4;
        }

        foreach ($params as $pid => &$opt)
        {
            $opt['key'] .= str_repeat(' ', $lkey - strlen($opt['key']));
            $opt['default'] .= str_repeat(
                ' ', $ldefault - strlen($opt['default'])
            );
            $opt['help'] = wordwrap(
                $opt['help'], $this->term_width - $lfull
            );

            if ($lfull === 4)
            {
                $opt['help'] = "\n{$opt['help']}\n";
            }

            $opt['help'] = str_replace(
                "\n", "\n" . str_repeat(' ', $lfull), $opt['help']
            );

            $opt = "  {$opt['key']} ".
                ($opt['default'] ? $opt['default'] . "  " : ' ').
                $opt['help'];
        }

        return implode("\n", $params);
    }

    /**
     * Set current state to invalid, and add message to the stack.
     * --
     * @param string $message
     * --
     * @return null
     */
    private function invalidate($message)
    {
        $this->is_valid = false;
        $this->messages[] = $message;
    }

    /**
     * Enforce type to value.
     * --
     * @param string $value
     * @param string $type
     * --
     * @return mixed
     */
    private function set_type($value, $type, $invert)
    {
        switch ($type)
        {
            case 'bool':
                $value = (bool) $value;
                return ($invert) ? !$value : $value;

            case 'int':
                $value = (int) $value;
                return ($invert) ? -($value) : $value;

            case 'float':
                $value = (float) $value;
                return ($invert) ? -($value) : $value;

            case 'arr':
            default:
                return (string) $value;
        }
    }

    /**
     * Find particular argument by type.
     * --
     * @param string $argument
     * @param string $type
     * --
     * @return mixed array if found, false if not
     */
    private function find($argument, $type)
    {
        foreach ($this->params as $id => $optv)
        {
            if ($type === 'positional' && $optv['positional'])
            {
                if (!isset($this->values[$id])) {
                    return $optv;
                }
            }
            elseif ($optv[$type] === $argument)
            {
                return $optv;
            }
        }

        return false;
    }

    /**
     * Validate options values.
     * --
     * @param array $options
     */
    private function options_validate(array $options)
    {
        $typesok = ['str', 'float', 'int', 'bool', 'arr'];

        // need a valid ID
        if (!$options['id'])
        {
            throw new \exception(
                "Invalid arguments! No valid ID.", 1
            );
        }

        // ID must be unique
        if (array_key_exists($options['id'], $this->params))
        {
            throw new \exception(
                "ID exists: `{$options['id']}`.", 2
            );
        }

        // if long...
        if ($options['long'])
        {
            // long need to be at lest 2 characters
            if (strlen($options['long']) < 2)
            {
                throw new \exception(
                    "Long argument need to be longer than one character: ".
                    "`{$options['long']}` for `{$options['id']}`.", 3
                );
            }

            if (strlen($options['long']) > 40)
            {
                throw new \exception(
                    "Long argument cannot be longer than 40 characters: ".
                    "`{$options['long']}` for `{$options['id']}`.", 3
                );
            }
        }

        if ($options['positional'] && $options['type'] === 'bool')
        {
            throw new \exception(
                "Positional arguments cannot have (bool) type".
                " `{$options['id']}`.", 5
            );
        }

        // if short...
        if ($options['short'])
        {
            // short cannot be more than one character
            if (strlen($options['short']) > 1)
            {
                throw new \exception(
                    "Short argument need to be one character long: ".
                    "`{$options['short']}` for `{$options['id']}`.", 10
                );
            }

            // short cannot be doubled
            foreach ($this->params as $pid => $popt)
            {
                if ($popt['short'] === $options['short'])
                {
                    throw new \exception(
                        "Short argument exists: ".
                        "`{$options['short']}` for `{$options['id']}` ".
                        "defined in `{$pid}`.", 12
                    );
                }
            }
        }

        // need to be valid type
        if (!in_array($options['type'], $typesok))
        {
            throw new \exception(
                "Invalid type: `{$options['type']}`. ".
                "Acceptable types: " . implode(', ', $typesok), 20
            );
        }

        // if default provided...
        if ($options['default'] !== null)
        {
            // if type bool, default needs to be bool
            if ($options['type'] === 'bool' && !is_bool($options['default']))
            {
                throw new \exception(
                    "Invalid default value for type `bool`. ".
                    "Require true/false.", 30
                );
            }
            // if type int, default needs to be int
            elseif ($options['type'] === 'int' && !is_int($options['default']))
            {
                throw new \exception(
                    "Invalid default value for type `int`. ".
                    "Require integer value.", 31
                );
            }
            // if float, default needs to be float
            elseif ($options['type'] === 'float' && !is_float($options['default']))
            {
                throw new \exception(
                    "Invalid default value for type `float`. ".
                    "Require float value.", 32
                );
            }
        }

        // if min > max
        if ($options['max'] !== null && $options['min'] !== null)
        {
            if ($options['min'] > $options['max'])
            {
                throw new \exception(
                    "Values for `min` ({$options['min']}) cannot be bigger ".
                    "than value for `max` ({$options['max']})", 40
                );
            }
        }
    }

    /**
     * Parse --long/-s || POSITIONAL to [positional, long, short]
     * --
     * @param string $name
     * --
     * @return array
     */
    private function parse_name_params($name)
    {
        $positional = false;
        $long       = null;
        $short      = null;

        if (!strpos($name, '/'))
        {
            if (substr($name, 0, 2) === '--')
            {
                $long = substr($name, 2);
            }
            elseif (substr($name, 0, 1) === '-')
            {
                $short = substr($name, 1);
            }
            else
            {
                $positional = strtolower($name);
            }
        }
        else
        {
            $segments = explode('/', $name);

            if (substr($segments[0], 0, 2) === '--')
            {
                $long = substr($segments[0], 2);
                $short = substr($segments[1], 1);
            }
            else
            {
                $long = substr($segments[1], 2);
                $short = substr($segments[0], 1);
            }
        }

        return [$positional, $long, $short];
    }
}
