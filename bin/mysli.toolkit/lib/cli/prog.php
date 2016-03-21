<?php

namespace mysli\toolkit\cli; class prog
{
    const __use = <<<fin
        .{ pkg }
        .cli.{ util, ui }
        .type.{ arr, str }
fin;

    /**
     * This is meta data for this particular CLI program.
     * It will be used when help is printed.
     * --
     * @var array
     */
    private $meta = [
        'title'            => null,
        'command'          => null,
        'description'      => null,
        'description_long' => null,
        'has_help'         => true,
        'version'          => false,
    ];

    /**
     * Weather passed arguments are valid.
     * --
     * @var boolean
     */
    private $is_valid = null;

    /**
     * Weather help was requested.
     * --
     * @var boolean
     */
    private $is_help = null;

    /**
     * Weather version was requested.
     * --
     * @var boolean
     */
    private $is_version = null;

    /**
     * Collection of messages, set on validation.
     * --
     * @var array
     */
    private $messages = [];

    /**
     * An array of arguments passed to this program.
     * --
     * @var array
     */
    private $arguments = [];

    /**
     * Collection of parameters this program accepts.
     * --
     * @var array
     */
    private $parameters = [];

    /**
     * Instance of PROG. Accepts basic meta data.
     * --
     * @param string $title
     * @param string $command
     */
    function __construct($title, $command=null)
    {
        $this->set_title($title);
        $this->set_command($command);
    }

    /**
     * Set program's title.
     * --
     * @param string $title
     */
    function set_title($title)
    {
        $this->meta['title'] = (string) $title;
    }

    /**
     * Set program's command (command being executed).
     * --
     * @param string $command In format: vendor.package.script or __CLASS__
     */
    function set_command($command)
    {
        // Resolve class.
        // From: vendor\class?\package\-root\-script\command
        // To:   vendor.class?.package.command
        if (strpos($command, '\\') !== false)
        {
            $namespace = explode('\\', $command);
            $command = array_splice($namespace, -1, 1)[0];
            $namespace = array_splice($namespace, 0, -2);

            // If command name is different than package name,
            // then add it to the namespace.
            if ($command !== $namespace[count($namespace)-1])
                $namespace[] = $command;

            $command = implode('.', $namespace);
        }

        $this->meta['command'] = (string) $command;
    }

    /**
     * Set program's short and long description to be displayed on HELP screen.
     * --
     * @param string $short
     * @param string $long
     */
    function set_description($short, $long=null)
    {
        $this->meta['description'] = (string) $short;
        $this->meta['description_long'] = (string) $long;
    }

    /**
     * Enable/disable help screen.
     * --
     * @param boolean $help
     */
    function set_help($help)
    {
        $this->meta['has_help'] = !! $help;
    }

    /**
     * Enable/disable version display.
     * --
     * @param string $version
     *        Either actual version, or vendor.package if acquire is true.
     *
     * @param boolean $acquire
     *        Version can be automatically read from mysli.pkg.ym
     */
    function set_version($version, $acquire=true)
    {
        if ($acquire && $version)
        {
            $version = pkg::get_version($version, true);
        }

        $this->meta['version'] = (string) $version;
    }

    /**
     * Create and assign new parameter.
     * --
     * @param string $id
     * @param array  $options
     * --
     * @throws \Exception 10 No such method.
     * --
     * @return self
     */
    function create_parameter($id, array $options)
    {
        $param = new param($id);

        // Assign options to parameter
        foreach ($options as $oid => $oval)
        {
            // Try to get option, if this will fail, exception will be thrown.
            $param->option($oid);

            // See if method exists
            if (!method_exists($param, $oid))
                throw new \Exception("No such method: `{$oid}`.", 10);

            $param->{$oid}($oval);
        }

        $this->parameters($param);

        return $this;
    }

    /**
     * Add parameter(s) which this program accepts.
     * --
     * @param \mysli\toolkit\cli\param $... Parameters.
     * --
     * @throws \Exception 10 Parameter needs to be of a `\\mysli\\toolkit\cli\\param` type.
     * @throws \Exception 20 Parameter with such (short) name already exists.
     * @throws \Exception 21 Parameter with such (long) name already exists.
     */
    function parameters()
    {
        $params = func_get_args();

        foreach ($params as $param)
        {
            if (!is_object($param) || !is_a($param, '\\mysli\\toolkit\\cli\\param'))
                throw new \Exception(
                    "Parameter needs to be of a `\\mysli\\toolkit\\cli\\param` type, got: `".
                    gettype($param)."`.", 10
                );
            else
            {
                // Duplicated short (-s)?
                if ($param->option('short') &&
                    $this->get_parameter('-'.$param->option('short')))
                    throw new \Exception(
                        "Parameter with such name already exists: `-".
                        $param->option('short')."`.", 20
                    );

                // Duplicated long (--long)?
                if ($param->option('long') &&
                    $this->get_parameter('--'.$param->option('long')))
                    throw new \Exception(
                        "Parameter with such name already exists: `-".
                        $param->option('long')."`.", 21
                    );

                $this->parameters[] = $param;
            }
        }
    }

    /**
     * Set arguments.
     * --
     * @param array $arguments
     */
    function arguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Check weather provided arguments meets the requirements of parameters.
     * --
     * @return boolean
     */
    function validate()
    {
        // Erase previous messages
        $this->messages = [];

        /*
        If help was set, set help to true and return.
        When help is passed in, result is not valid nor invalid, hence null.
         */
        if (isset($this->arguments[0]) &&
            in_array($this->arguments[0], ['-h', '--help']))
        {
            $this->is_help = true;
            $this->is_valid = null;
            return true;
        }

        /*
        If version was set, set version to true and return.
        When version is passed in, result is not valid nor invalid, hence null.
         */
        if (isset($this->arguments[0]) &&
            in_array($this->arguments[0], ['--version']))
        {
            $this->is_version = true;
            $this->is_valid = null;
            return true;
        }

        // Count of arguments
        $cargs = count($this->arguments);

        // Current positional parameter position
        // dot foo bar baz
        //      ^1  ^2  ^3
        $positional = 1;

        // Match parameter name (including parameter with values e.g. --foo=bar)
        $vp_name = '/^((?:--[a-z]{1}(?:\-{0,1}[a-z0-9]+)+)|(?:-[a-z]+))(?:\=(.*?))?$/i';

        /*
        Go through list of arguments.
         */
        for ($i=0; $i < $cargs; $i++)
        {
            // Set current parameter
            $current = $this->arguments[$i];

            // If short + multiple together (e.g. -wps) then extract them
            if (strlen($current) > 2 && substr($current, 0, 1) === '-' &&
                substr($current, 0, 2) !== '--')
            {
                $current = str_split(substr($current, 1));
                foreach ($current as &$c) $c = "-{$c}";
                $cargs += count($current)-1;
                array_splice($this->arguments, $i, 1, $current);
                $current = arr::first($current);
            }


            if (preg_match($vp_name, $current, $match))
            {
                /*
                Named e.g. --long/-s
                 */

                // Get parameter if exists
                $parameter = $this->get_parameter($match[1]);
                $value = isset($match[2]) ? $match[2] : null;

                // Couldn't find such parameter
                if (!$parameter)
                {
                    $this->invalidate("Invalid parameter name: `{$match[1]}`.");
                    return false;
                }

                // In all cases, except in a case of boolean, value is needed
                if ($parameter->option('type') !== 'boolean')
                {
                    // Value wasn't assigned with = sign
                    if (!$value)
                    {
                        // Increase value of $i by one, this will skip over
                        // value in next loop.
                        $i = $i + 1;

                        // Value is required if not boolean
                        if (!isset($this->arguments[$i]))
                        {
                            $this->invalidate(
                                "Parameter: `{$match[1]}` excepts value, none got."
                            );
                            return false;
                        }

                        // Assign next argument to the value
                        $value = $this->arguments[$i];
                    }
                }
                else
                {
                    // This is a boolean value, so assign it to true
                    $value = $value ?: true;
                }
            }
            else
            {
                /*
                Positional
                 */
                $parameter = $this->get_parameter(":{$positional}");

                // Couldn't find positional parameter
                if (!$parameter)
                {
                    $this->invalidate(
                        "No value expected at: `:{$positional} ({$current})`."
                    );
                    return false;
                }

                // Set value to be current.
                $value = $current;

                $positional++;
            }

            // Try to set parameter's value
            try
            {
                // Assign value to the parameter
                $parameter->set_value($value);
            }
            catch (\Exception $e)
            {
                $this->invalidate($e->getMessage());
                return false;
            }
        }

        // See if any required parameters were left unset
        foreach ($this->parameters as $param)
        {
            if ($param->option('required') && !$param->is_set())
            {
                $this->invalidate(
                    "Parameter `".$param->option('name')."` is required."
                );
                return false;
            }
        }

        // If we came so far, everything must be fine
        return true;
    }

    /**
     * Invalidate this prog's arguments. Will set `is_valid` to false, and
     * add message with explanation why validation failed.
     * --
     * @param  string $message
     */
    function invalidate($message)
    {
        $this->messages[] = $message;
        $this->is_valid = false;
    }

    /**
     * Weather this arguments provided to this PROG are valid.
     * --
     * @return boolean
     */
    function is_valid()
    {
        return $this->is_valid;
    }

    /**
     * Weather help was required, and should be displayed.
     * --
     * @return boolean
     */
    function is_help()
    {
        return $this->is_help;
    }

    /**
     * Actually return help string.
     * --
     * @param boolean $style
     *        Weather to style the output.
     * --
     * @return string
     */
    function help($style=true)
    {
        /*
        Set defaults.
         */
        $command = $this->meta['command'] ?: "COMMAND";
        $title   = $this->meta['title'];
        $description = $this->meta['description'];
        $description_long = $this->meta['description_long'];

        $sargs = $this->format_param_simple();
        $pargs = $this->format_param_detailed('positional');
        $dargs = $this->format_param_detailed();

        $terminal_width = util::terminal_width() ?: 75;
        $dargs_title = 'Options:';

        /*
        Assemble the description.
         */
        $description = $description
            ? (strlen($description) > $terminal_width
                ? substr($description, 0, ($terminal_width-3)).'...'
                : $description)
            : '';

        /*
        Other options
         */
        $oargs_title = 'Other:';
        $oargs = '';
        if ($this->meta['version'])
        {
            $oargs .= "    --version  Display version information.\n";
        }
        $oargs .= "-h, --help     Display this help.";

        /*
        Should output be styled?
         */
        if ($style)
        {
            ui::buffer();
            ui::title($title);
            $title = ui::flush(true);

            if ($dargs)
            {
                ui::buffer();
                ui::strong($dargs_title);
                $dargs_title = ui::flush(true);
            }

            ui::buffer();
            ui::strong($oargs_title);
            $oargs_title = ui::flush(true);
        }

        $output =
            ($title ? "{$title}" : '').
            ($description ? "{$description}\n" : '').
            "\nUsage: ./dot {$command} {$sargs}\n".
            ($pargs ? "\n{$pargs}\n" : '').
            ($dargs ? "\n{$dargs_title}\n{$dargs}\n" : '').
            "\n{$oargs_title}\n{$oargs}\n".
            ($description_long
                ? "\n".wordwrap($description_long, $terminal_width)."\n"
                : ''
            );

        return trim($output, "\n");
    }

    /**
     * Weather version was required, and should be displayed.
     * --
     * @return boolean
     */
    function is_version()
    {
        return $this->is_version;
    }

    /**
     * Actually return version string.
     * --
     * @return string
     */
    function version()
    {
        return $this->meta['title'].' Version '.$this->meta['version'];
    }

    /**
     * Get all messages added on validation.
     * --
     * @return string
     */
    function messages()
    {
        return implode("\n", $this->messages);
    }

    /**
     * Get parameter's option, at index.
     * --
     * @example
     *     // In case of: mysli prog --foo bar --faz fez foz
     *     $prog->get_parameter_at(0, 'name'); // => foo
     * --
     * @param integer $index
     *
     * @param string  $option Options name.
     *
     * @param boolean $is_set
     *        Parameter needs to be set, skip those which are not set!
     * --
     * @return mixed
     */
    function get_option_at($index, $option, $is_set=true)
    {
        // Count of parameters which actually has value
        $sid = 0;

        foreach ($this->parameters as $aid => $param)
        {
            if (!$is_set || $param->is_set())
            {
                if ((!$is_set && $aid === $index) || ($sid === $index))
                {
                    if ($option === 'value')
                        return $param->get_value();
                    else
                        return $param->option($option);
                }
                else
                {
                    $sid++;
                }
            }
        }
    }

    /**
     * Get multiple values as an array.
     * --
     * @param string $... (@see static::get_parameter())
     * --
     * @return array
     */
    function get_values()
    {
        $params = func_get_args();
        $result = [];

        foreach ($params as $param)
        {
            $result[] = $this->get_parameter($param)->get_value();
        }

        return $result;
    }

    /**
     * Get multiple parameters as an array.
     * --
     * @param string $... (@see static::get_parameter())
     * --
     * @return array
     */
    function get_parameters()
    {
        $params = func_get_args();
        $result = [];

        foreach ($params as $param)
        {
            $result[] = $this->get_parameter($param);
        }

        return $result;
    }

    /**
     * Get parameter by name, long, short, positional or index.
     * --
     * @param mixed $id
     *        integer: get at index.
     *        string:  by name, or if it's --foo then long, -s short
     *        string:  :0, :1 get positional parameter at selected position
     * --
     * @return \mysli\toolkit\cli\param
     */
    function get_parameter($id)
    {
        /*
        Get by index
         */
        if (is_integer($id))
        {
            if (isset($this->parameters[$id]))
                return $this->parameters[$id];
            else
                return null;
        }
        if (substr($id, 0, 1) === ':')
        {
            /*
            Get as positional
             */

            $id = (int) substr($id, 1);

            // Current positional ID
            $cid = 1;

            foreach ($this->parameters as $param)
            {
                if ($param->option('positional'))
                {
                    if ($cid === $id)
                        return $param;
                    else
                        $cid++;
                }
            }
        }
        else
        {
            /*
            Get by long/short/name
             */

            if (substr($id, 0, 2) === '--')
            {
                $type = 'long';
                $id = substr($id, 2);
            }
            else if (substr($id, 0, 1) === '-')
            {
                $type = 'short';
                $id = substr($id, 1);
            }
            else
            {
                $type = 'name';
            }

            foreach ($this->parameters as $param)
            {
                if ($param->option($type) === $id)
                    return $param;
            }
        }
    }

    /*
    --- Protected --------------------------------------------------------------
     */

    /**
     * Format one-line parameter representation.
     * --
     * @return string
     */
    private function format_param_simple()
    {
        // Final return value.
        $return = '';

        // Amount of non-positional parameters.
        $count = 0;

        // Amount of required parameters.
        $required = 0;

        // List of positional parameters.
        $positionals = [];

        /*
        Go through all parameters.
         */
        foreach ($this->parameters as $param)
        {
            if (!$param->option('positional'))
            {
                $count++;
                $required += $param->option('required');
            }
            else
            {
                $positionals[] = $param->option('required')
                    ? strtoupper($param->option('id'))
                    : '[' . strtoupper($param->option('id')) . ']';
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
     * Format detailed list of parameters for help screen.
     * --
     * @param string $type null|positional
     * --
     * @return string
     */
    private function format_param_detailed($type=null)
    {
        // Meta for each parameter
        $params = [];

        // Longest key
        $lkey = 0;

        // Longest default value
        $ldefault = 0;

        // Actual terminal width
        $terminal_width = util::terminal_width() ?: 75;

        // Maximum line width
        $lmax = $terminal_width - 15;

        /*
        Process parameters
         */
        foreach ($this->parameters as $param)
        {
            // Skip if only positional are required,
            // and this is not positional, or vice versa.
            if (($type === 'positional' && !$param->option('positional')) ||
                ($type !== 'positional' &&  $param->option('positional')))
            {
                continue;
            }

            // Assign PID for easy access
            $pid = $param->option('id');

            // Set sort/long, default for easy access
            $short   = $param->option('short');
            $long    = $param->option('long');
            $default = $param->option('def');

            // Key is the left side on a list of command when help is displayed.
            if ($param->option('positional'))
            {
                // In case of positional, capitalize key.
                $params[$pid]['key'] = strtoupper($pid);
            }
            else
            {
                if ($long && $short)
                    $params[$pid]['key'] = "-{$short}, --{$long}";
                elseif ($long)
                    $params[$pid]['key'] = "    --{$long}";
                else
                    $params[$pid]['key'] = "-{$short}";
            }

            // Default value is represented as: --long [default] Description
            if ($default !== null)
            {
                if (is_bool($default))
                {
                    $params[$pid]['default'] = $default ? '[true]' : '[false]';
                }
                elseif (is_array($default))
                {
                    $odef = implode(',', $default);
                    $odef = str::limit_length($odef, 13);
                    $params[$pid]['default'] = "[{$odef}]";
                }
                else
                {
                    $odef = str::limit_length($default, 13);
                    $params[$pid]['default'] = "[{$odef}]";
                }
            }
            else
            {
                $params[$pid]['default'] = '';
            }

            // Actual description of parameter
            $params[$pid]['help'] = $param->option('help');

            // Adjust longest key length
            if (strlen($params[$pid]['key']) > $lkey)
                $lkey = strlen($params[$pid]['key']);

            // Adjust longest default length
            if (strlen($params[$pid]['default']) > $ldefault)
                $ldefault = strlen($params[$pid]['default']);
        }

        // Full length of key + default + spaces:
        // --key [default]
        $lfull = ($lkey + 2) + ($ldefault ? $ldefault + 3 : 2);

        // If too long, new line
        if ($lfull > $lmax)
            $lfull = 4;

        /*
        Actually assemble help string.
         */
        foreach ($params as $pid => &$opt)
        {
            $opt['key'] .= str_repeat(' ', $lkey - strlen($opt['key']));

            $opt['default'] .= str_repeat(
                ' ', $ldefault - strlen($opt['default'])
            );

            $opt['help'] = wordwrap(
                $opt['help'], $terminal_width - $lfull
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

    /*
    --- Static -----------------------------------------------------------------
     */

    /**
     * Validate prog and print messages to CLI if failed
     * or if help or version was requested.
     * --
     * @param \mysli\toolkit\prog $prog
     * @param array               $arguments
     * --
     * @return boolean
     *         NULL  if successfull (nothing happened).
     *         TRUE  if help or version was printed.
     *         FALSE if failed.
     */
    static function validate_and_print(self $prog, array $arguments=null)
    {
        if ($arguments)
            $prog->arguments($arguments);

        if (!$prog->validate())
        {
            ui::warning('WARNING', $prog->messages());
            return false;
        }
        else if ($prog->is_help())
        {
            ui::line($prog->help());
            return true;
        }
        else if ($prog->is_version())
        {
            ui::line($prog->version());
            return true;
        }
        else
        {
            return null;
        }
    }
}
