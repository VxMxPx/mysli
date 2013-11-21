<?php

namespace Mysli\Core\Lib;

class Dot
{
    protected $scripts_registry;
    protected $constructed = [];

    public function __construct(array $scripts_registry)
    {
        $this->scripts_registry = $scripts_registry;
    }

    protected function construct_script($script) {
        if (isset($this->constructed[$script])) {
            return $this->constructed[$script];
        }
        $class = $this->scripts_registry[$script]['class'];
        $class_array = explode('\\', $class);
        $library = \Librarian::ns_to_lib($class_array[0] . '\\' . $class_array[1]);
        if (!class_exists($class, false)) {
            if (!\Librarian::is_enabled($library)) {
                \DotUtil::warn('FAILED. Not enabled: ' . $library);
            }
            $path = \Str::to_underscore($class);
            $path = str_replace('\\', '/', $path);
            $path = preg_replace('/\/script\//', '/scripts/', $path);
            $path = libpath($path . '.php');
            if (!file_exists($path)) {
                \DotUtil::warn("Cannot find script: `{$path}`.");
                return false;
            }
            include $path;
        }
        if (!class_exists($class, false)) {
            \DotUtil::warn("Cannot find class: `{$class}` in `{$path}`.");
            return false;
        }

        $dependencies = \Librarian::dependencies_factory($library);
        return new $class($dependencies);
    }

    public function execute($script, $command, array $arguments = [])
    {
        if (!isset($this->scripts_registry[$script])) {
            \DotUtil::warn('Command not found: ' . $script);
            return false;
        }
        $script_obj = $this->construct_script($script);
        if (!is_object($script_obj)) {
            \DotUtil::warn('Could not construct the object!');
            return false;
        }
        if (!$command) {
            $command = 'index';
        } elseif ($command === '--help') {
            if (method_exists($script_obj, 'help_index')) {
                call_user_func([$script_obj, 'help_index']);
            } else {
                \DotUtil::plain("No help found for command `{$script}`.");
            }
            return true;
        }
        if (method_exists($script_obj, 'action_' . $command)) {
            call_user_func_array([$script_obj, 'action_' . $command], $arguments);
            return true;
        } elseif (method_exists($script_obj, 'help_' . $command)) {
            call_user_func([$script_obj, 'help_' . $command]);
        }
        return false;
    }

    public function list_scripts()
    {
        $commands = [];
        foreach ($this->scripts_registry as $script => $data) {
            $commands[] = [
                $script,
                $data['description']
            ];
        }
        \DotUtil::doc(
            'Mysli Core :: List of Available Commands',
            '<COMMAND> [OPTIONS...]',
            $commands
        );
        return true;
    }
}
