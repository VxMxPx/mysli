<?php

namespace Mysli\Dot;

class Dot
{
    protected $scripts_registry;
    protected $constructed = [];
    protected $pkgm;

    /**
     * Construct DOT
     * --
     * @param object $pkgm
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
        $scripts = [];

        foreach ($this->pkgm->registry()->list_enabled(true) as $package => $details) {
            $path = pkgpath($package . '/script');
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
                    'package'     => $package,
                    'script'      => ds('script', $id),
                    'description' => $details['about']['description']
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
        } else {
            $this->list_scripts();
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

        $script_info = $this->scripts_registry[$script];

        $factory = $this->pkgm->factory($script_info['package']);
        $script_obj = $factory->produce($script_info['script']);

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
