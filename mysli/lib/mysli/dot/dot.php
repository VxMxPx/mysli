<?php

namespace Mysli;

use \Mysli\Dot\Util as Util;

class Dot
{
    protected $scripts_registry;
    protected $constructed = [];
    protected $librarian;

    /**
     * Construct DOT
     * --
     * @param object $librarian ~librarian
     */
    public function __construct($librarian)
    {
        $this->librarian = $librarian;
        $this->scripts_registry = $this->discover_scripts();

        // Remove vendor for non-specific usage in scripts we're calling.
        class_alias('\\Mysli\\Dot\\Util', 'Dot\\Util');
    }

    /**
     * Scan libraries to find scripts.
     * --
     * @return array
     */
    protected function discover_scripts()
    {
        $enabled = $this->librarian->get_enabled(true);
        $scripts = [];

        foreach ($enabled as $library => $details) {
            $path = libpath($library . '/scripts');
            if (!file_exists($path) || !is_dir($path)) {
                continue;
            }
            $files = scandir($path);
            foreach ($files as $file) {
                if (substr($file, -4) !== '.php') {
                    continue;
                }
                $id = substr($file, 0, -4);
                $scripts[$id] = [
                    'path'        => ds($path, $file),
                    'class'       =>
                        $this->librarian->lib_to_ns($library) .
                        '\\Script\\' . \Str::to_camelcase($id),
                    'description' => $details['description']
                ];
            }
        }

        return $scripts;
    }


    /**
     * Will run command (arguments)
     * --
     * @param  array  $arguments
     * --
     * @return void
     */
    public function run(array $arguments)
    {
        if (isset($arguments[1])) {
            $script  = $arguments[1];
            if ($script === '--help') {
                $this->list_scripts();
            }
            $command = isset($arguments[2]) ? $arguments[2] : false;
            if (!$this->execute($script, $command, array_slice($arguments, 3))) {
                Util::warn('Cannot find the command: ' . $script);
            }
        } else {
            $this->list_scripts();
        }
    }

    /**
     * Construct required script
     * --
     * @param  string $script
     * --
     * @return object
     */
    protected function construct_script($script)
    {
        if (isset($this->constructed[$script])) {
            return $this->constructed[$script];
        }
        $class = $this->scripts_registry[$script]['class'];
        $class_array = explode('\\', $class);
        $library = $this->librarian->ns_to_lib($class_array[0] . '\\' . $class_array[1]);
        if (!class_exists($class, false)) {
            if (!$this->librarian->is_enabled($library)) {
                Util::warn('FAILED. Not enabled: ' . $library);
            }
            $path = \Str::to_underscore($class);
            $path = str_replace('\\', '/', $path);
            $path = preg_replace('/\/script\//', '/scripts/', $path);
            $path = libpath($path . '.php');
            if (!file_exists($path)) {
                Util::warn("Cannot find script: `{$path}`.");
                return false;
            }
            include $path;
        }
        if (!class_exists($class, false)) {
            Util::warn("Cannot find class: `{$class}` in `{$path}`.");
            return false;
        }

        $libname = explode('/', $library)[1];

        $object = new \ReflectionClass($class);
        if ($object->hasMethod('__construct')) {
            $dependencies = $this->dependencies_factory($library);
            $dependencies[]['requested_by'] = $library;
            return $object->newInstanceArgs($dependencies);
        } else {
            return $object->newInstanceWithoutConstructor();
        }
    }

    /**
     * Execute particular script.
     * --
     * @param  string $script
     * @param  string $command
     * @param  array  $arguments
     * --
     * @return boolean
     */
    public function execute($script, $command, array $arguments = [])
    {
        if (!isset($this->scripts_registry[$script])) {
            Util::warn('Command not found: ' . $script);
            return false;
        }
        $script_obj = $this->construct_script($script);
        if (!is_object($script_obj)) {
            Util::warn('Could not construct the object!');
            return false;
        }
        if (!$command) {
            $command = 'index';
        } elseif ($command === '--help') {
            if (method_exists($script_obj, 'help_index')) {
                call_user_func([$script_obj, 'help_index']);
            } else {
                Util::plain("No help found for command `{$script}`.");
            }
            return true;
        }
        if (method_exists($script_obj, 'action_' . $command)) {
            call_user_func_array([$script_obj, 'action_' . $command], $arguments);
            return true;
        } elseif (method_exists($script_obj, 'help_' . $command)) {
            call_user_func([$script_obj, 'help_' . $command]);
            return true;
        }
        return false;
    }

    /**
     * List all available scripts.
     * --
     * @return void
     */
    public function list_scripts()
    {
        $commands = [];
        foreach ($this->scripts_registry as $script => $data) {
            $commands[$script] = $data['description'];
        }
        Util::doc(
            'Mysli Core :: List of Available Commands',
            '<COMMAND> [OPTIONS...]',
            $commands
        );
        return true;
    }
}
