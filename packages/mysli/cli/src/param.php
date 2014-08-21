<?php

namespace mysli\cli {

    use \mysli\base\str as str;
    use \mysli\base\arr as arr;

    class param {
        protected $title = null;
        protected $description = null;
        protected $params = [];
        protected $params_s = [];
        protected $values = [];
        protected $messages = [];
        protected $is_valid = false;

        protected $defaults = [
            'type'       => 'str',
            'default'    => null,
            'help'       => null,
            'required'   => false,
            'positional' => false,
            'action'     => false
        ];

        /**
         * Construct cli/param
         * @param string $title
         */
        function __construct($title=null) {
            $this->title($title);
        }
        /**
         * Set title (to be displayed in help)
         * @param  string $title
         */
        function title($title) {
            $this->title = $title;
        }
        /**
         * Set description (to be displayed in help)
         * @param  string $description
         */
        function description($description) {
            $this->description = $description;
        }
        /**
         * Add parameter
         * @param string $name long/short e.g.: long/s (--long -s) or only long
         * @param array  $options
         */
        function add($name, array $options) {
            list($long, $short) = $this->parse_name_params($name);
            $options = arr::merge($this->default, $options);
            $options['long']  = $long;
            $options['short'] = $short;

            $this->options_validate($options);

            if ($short) {
                $this->params_s[] = $short;
            }

            $this->params[$long] = $options;
        }
        /**
         * Return list of messages, which are set after parse()
         * @return string
         */
        function messages() {
            return implode("\n", $this->messages);
        }
        /**
         * Return status (weater execution was valid, all required parameters
         * were set, etc...)
         * @return boolean
         */
        function is_valid() {
            return $this->is_valid;
        }
        /**
         * Get list of values
         * @return array
         */
        function values() {
            return $this->values;
        }
        /**
         * Parse all parameters
         * @return void
         */
        function parse() {
            $this->values = [];
            $this->messages = [];
            $this->is_valid = false;

            $shortlist = '';
            $longlist = [];

            foreach ($this->params as $id => $options) {
                if ($options['short']) {
                    if ($options['required']) {
                        $shortlist = $options['short'] . ':' . $shortlist;
                    } else {
                        $shortlist .= $options['short'];
                    }
                }
                $longlist[] = $options['long'] .
                    ($options['required'] ? ':' : '');
            }

            dump($shortlist, $longlist);
        }

        // protected

        /**
         * Validate options values
         * @param  array $options
         */
        protected function options_validate(array $options) {

            $typesok = ['str', 'float', 'int', 'bool'];

            if (!$options['long']) {
                throw new core\exception\value(
                    "Long parameter is required: `{$name}`.", 1);
            }
            if (arr::key_in($this->params, $options['long'])) {
                throw new core\exception\value(
                    "Long parameter exists: ".
                    "`{$options['long']}` for `{$name}`.", 2);
            }
            if ($options['short']) {
                if ($options['positional']) {
                    throw new core\exeption\value(
                        "Positional parameters " .
                        "cannot have short version: `{$name}`.", 3);
                }
                if (in_array($options['short'], $this->params_s)) {
                    throw new core\exeption\value(
                        "Short parameter exists: ".
                        "`{$options['short']}` for `{$name}`.", 4);
                }
            }
            if (!in_array($options['type'], $typesok)) {
                throw new core\exception\value(
                    "Invalid type: `{$options['type']}`. ".
                    "Acceptable types: " . implode(', ', $typesok), 5);
            }
            if ($options['default'] !== null) {
                if ($options['type'] === 'bool' &&
                    !is_bool($options['default'])) {
                    throw new core\exception\value(
                        "Invalid default value for type `bool`. ".
                        "Require true/false.", 20);
                }
                elseif ($options['type'] === 'int' &&
                    !is_int($options['default'])) {
                    throw new core\exception\value(
                        "Invalid default value for type `int`. ".
                        "Require integer value.", 21);
                }
                elseif ($options['type'] === 'float' &&
                    !is_float($options['default'])) {
                    throw new core\exception\value(
                        "Invalid default value for type `float`. ".
                        "Require float value.", 22);
                }
                elseif ($options['type'] === 'str' &&
                    !is_string($options['default'])) {
                    throw new core\exception\value(
                        "Invalid default value for type `str`. ".
                        "Require string value.", 23);
                }
            }
        }
        /**
         * Parse long/s to [long, short]
         * @param  string $name
         * @return array
         */
        protected function parse_name_params($name) {
            $short = null;
            $long = null;
            $seg = explode('/', $name, 2);
            if (str::length($seg[0]) === 1) {
                arr::prepend($seg, null);
            }
            if (count($seg) < 2) {
                $seg[] = null;
            }
            return [$seg[0], $seg[1]];
        }
    }
}
