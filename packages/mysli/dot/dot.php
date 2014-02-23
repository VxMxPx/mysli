<?php

namespace Mysli;

class Dot
{
    protected $scripts_registry;
    protected $constructed = [];
    protected $pkgm;

    /**
     * Construct DOT
     * --
     * @param object $pkgm ~pkgm
     */
    public function __construct($pkgm)
    {
        $this->pkgm = $pkgm;
        $this->scripts_registry = $this->discover_scripts();

        // Remove vendor for non-specific usage in scripts we're calling.
        class_alias('\\Mysli\\Dot\\Util', 'Cli\\Util');
    }

    /**
     * Scan packages to find scripts.
     * --
     * @return array
     */
    protected function discover_scripts()
    {
        $enabled = $this->pkgm->get_enabled(true);
        $scripts = [];

        foreach ($enabled as $package => $details) {
            $path = pkgpath($package . '/scripts');
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
                        $this->pkgm->pkg_to_ns($package) .
                        '\\Script\\' . \Core\Str::to_camelcase($id),
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
            $this->execute($script, $command, array_slice($arguments, 3));
            // if (!$this->execute($script, $command, array_slice($arguments, 3))) {
            //     \Cli\Util::warn('Cannot find the command: ' . $script);
            // }
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
        $package = $this->pkgm->ns_to_pkg($class_array[0] . '\\' . $class_array[1]);
        if (!class_exists($class, false)) {
            if (!$this->pkgm->is_enabled($package)) {
                \Cli\Util::warn('FAILED. Not enabled: ' . $package);
            }
            $path = \Core\Str::to_underscore($class);
            $path = str_replace('\\', '/', $path);
            $path = preg_replace('/\/script\//', '/scripts/', $path);
            $path = pkgpath($path . '.php');
            if (!file_exists($path)) {
                \Cli\Util::warn("Cannot find script: `{$path}`.");
                return false;
            }
            include $path;
        }
        if (!class_exists($class, false)) {
            \Cli\Util::warn("Cannot find class: `{$class}` in `{$path}`.");
            return false;
        }

        //$pkgname = explode('/', $package)[1];

        $object = new \ReflectionClass($class);
        if ($object->hasMethod('__construct')) {
            $info = $this->pkgm->get_details($package);
            $dependencies_list = array_key_exists('script', $info['inject'])
                ? $info['inject']['script']
                : $info['inject']['main'];
            // Do we need main class?
            $dependencies = $this->pkgm->dependencies_factory($dependencies_list);
            if (array_key_exists('#main', $dependencies)) {
                $dependencies['#main'] = $this->pkgm->factory($package);
            }
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
            \Cli\Util::warn('Command not found: ' . $script);
            return false;
        }
        $script_obj = $this->construct_script($script);
        if (!is_object($script_obj)) {
            \Cli\Util::warn('Could not construct the object!');
            return false;
        }
        if (!$command) {
            $command = 'index';
        } elseif ($command === '--help') {
            if (method_exists($script_obj, 'help_index')) {
                call_user_func([$script_obj, 'help_index']);
            } else {
                \Cli\Util::plain("No help found for command `{$script}`.");
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
        \Cli\Util::doc(
            'Mysli Core :: List of Available Commands',
            '<COMMAND> [OPTIONS...]',
            $commands
        );
        return true;
    }
}
