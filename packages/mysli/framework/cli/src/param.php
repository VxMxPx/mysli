<?php

namespace mysli\framework\cli;

__use(__namespace__, '
    mysli.framework.exception/* -> framework\exception\*
    mysli.framework.type/str,arr
');

class param
{
    private $term_width;

    private $params = [];
    private $values = [];
    private $messages = [];
    private $is_valid = false;

    public $title = null;
    public $command = null;
    public $description = null;
    public $description_long = null;

    private $arguments = [];

    private $defaults = [
        'id'         => null,  // unique id (position || long || short)
        'short'      => null,  // short (-s)
        'long'       => null,  // long (--long)
        'type'       => 'str', // type (str,bool,int,float,arr)
        'min'        => null,  // minimum value when integer or float,
                               // minimum amount of elements when arr
        'max'        => null,  // maximum value, when integer or float,
                               // maximum amount of elements when arr
        'default'    => null,  // default value, if no argument provided.
                               // default will also be used, if argument
                               // if present, has no value assigned, e.g.:
                               // --param --param2
        'help'       => null,  // help text
        'required'   => null,  // weather field is required
        'positional' => false, // weather this is positional parameter
                               // this will be automatically set by id
        'allow_empty'=> false, // weather empty values are accepted.
                               // if no default, then this would mean:
                               // string: '', bool: false, int: 0,
                               // float: 0.0
        'exclude'    => false, // which arguments cannot be set in
                               // combination with this one (array)
        'invoke'     => false, // if parameter is present a provided method
                               // will be executed
        'action'     => false, // func to be executed when field is parsed:
                               // value, is_valid, messages, break=false
        'invert'     => false, // invert bool value
        'ignore'     => false  // weather value should be ignored
    ];

    /**
     * Construct cli/param
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
            'type'   => 'bool',
            'help'   => 'Display this help',
            'ignore' => true,
            'action' => function ($val, &$is_valid, &$messages) {
                $is_valid = false;
                $messages = [];
                $messages[] = $this->help();
                return false;
            }
        ]);
    }
    /**
     * Add parameter
     * @param string $name long/short e.g.: long/s (--long -s) or only long
     * @param array  $options
     */
    function add($name, array $options = [])
    {
        list($positional, $long, $short) = $this->parse_name_params($name);
        $options = arr::merge($this->defaults, $options);
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
     * @return string
     */
    function messages()
    {
        return implode("\n", $this->messages);
    }
    /**
     * Return status (weater execution was valid, all required parameters
     * were set, etc...)
     * @return boolean
     */
    function is_valid()
    {
        return $this->is_valid;
    }
    /**
     * Get list of values
     * @return array
     */
    function values()
    {
        return $this->values;
    }
    /**
     * Return list of currently set parameters.
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
     * Parse all parameters
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

            if ($opt['invoke'] && isset($this->values[$pid]))
            {
                $invoke[$pid] = $opt['invoke'];
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
        // check if any parameters to be invoked is present
        // we need seperate loop for this, as all values needs to be set
        foreach ($invoke as $pid => $call)
        {
            call_user_func_array($call, [$this->values[$pid], $this->values]);
        }

        return true;
    }
    /**
     * Return full (auto-generated) help.
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
            ($this->title ? "{$this->title}\n" : '').
            ($this->description ? "{$description}\n" : '').
            "\nUsage: ./dot {$command} {$sargs}\n".
            ($pargs ? "\n{$pargs}\n" : '').
            ($dargs ? "\nOptions:\n{$dargs}\n" : '').
            ($this->description_long
                ? "\n".wordwrap($this->description_long, $this->term_width)."\n"
                : ''
            );
    }

    // protected

    /**
     * Set null value for particular type:
     *   str   : ''
     *   int   : 0
     *   float : 0.0
     *   bool  : false
     *   arr   : []
     * @param   string $type
     * @return  mixed
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
     * @param  string  $arg
     * @param  string  $type
     * @param  boolean $get_current
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
     * @param   string  $arg
     * @param   array   $opt
     * @return  boolean
     */
    private function set_value($arg, array $opt)
    {
        $value = $this->set_type($arg, $opt['type'], $opt['invert']);
        $rt = true;

        if (is_callable($opt['action']))
        {
            $rt = $opt['action']($value, $this->is_valid, $this->messages);
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
     * @param  string $type null|positional
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
     * @param  string $message
     * @return null
     */
    private function invalidate($message)
    {
        $this->is_valid = false;
        $this->messages[] = $message;
    }
    /**
     * Enforce type to value.
     * @param  string $value
     * @param  string $type
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
     * @param  string $argument
     * @param  string $type
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
     * Validate options values
     * @param  array $options
     */
    private function options_validate(array $options)
    {
        $typesok = ['str', 'float', 'int', 'bool', 'arr'];

        // need a valid ID
        if (!$options['id'])
        {
            throw new framework\exception\argument(
                "Invalid arguments! No valid ID.", 1
            );
        }

        // ID must be unique
        if (arr::key_in($this->params, $options['id']))
        {
            throw new framework\exception\argument(
                "ID exists: `{$options['id']}`.", 2
            );
        }

        // if long...
        if ($options['long'])
        {
            // long need to be at lest 2 characters
            if (strlen($options['long']) < 2)
            {
                throw new framework\exception\argument(
                    "Long argument need to be longer than one character: ".
                    "`{$options['long']}` for `{$options['id']}`.", 3
                );
            }

            if (strlen($options['long']) > 40)
            {
                throw new framework\exception\argument(
                    "Long argument cannot be longer than 40 characters: ".
                    "`{$options['long']}` for `{$options['id']}`.", 3
                );
            }
        }

        if ($options['positional'] && $options['type'] === 'bool')
        {
            throw new framework\exception\argument(
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
                throw new framework\exception\argument(
                    "Short argument need to be one character long: ".
                    "`{$options['short']}` for `{$options['id']}`.", 10
                );
            }

            // short cannot be doubled
            foreach ($this->params as $pid => $popt)
            {
                if ($popt['short'] === $options['short'])
                {
                    throw new framework\exception\argument(
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
            throw new framework\exception\argument(
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
                throw new framework\exception\argument(
                    "Invalid default value for type `bool`. ".
                    "Require true/false.", 30
                );
            }
            // if type int, default needs to be int
            elseif ($options['type'] === 'int' && !is_int($options['default']))
            {
                throw new framework\exception\input(
                    "Invalid default value for type `int`. ".
                    "Require integer value.", 31
                );
            }
            // if float, default needs to be float
            elseif ($options['type'] === 'float' && !is_float($options['default']))
            {
                throw new framework\exception\input(
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
                throw new framework\exception\argument(
                    "Values for `min` ({$options['min']}) cannot be bigger ".
                    "than value for `max` ({$options['max']})", 40
                );
            }
        }
    }
    /**
     * Parse --long/-s || POSITIONAL to [positional, long, short]
     * @param  string $name
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
