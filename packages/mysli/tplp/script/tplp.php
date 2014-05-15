<?php

namespace Mysli\Tplp\Script;

class Tplp
{
    private $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Print general help.
     */
    public function help_index()
    {
        \Cli\Util::doc(
            'Mysli Tplp :: Template Parser',
            'tplp <OPTION> [ARGUMENTS...]',
            [
                'observe <PACKAGE> [DIRECTORY]' =>
                    'Observe package\'s templates for changes, and rebuild, if changes are detected.',
            ]
        );

        return true;
    }

    public function action_observe($package = null, $directory = 'templates')
    {
        $path = pkgpath($package, $directory);
        if (!is_dir($path)) {
            \Cli\Util::warn(
                'Tplp: Not a valid package: `' . $package .
                '`, or template directory doesn\'t exists: `' . $directory . '`.'
            );
            return false;
        }

        \Cli\Util::success('Tplp: Package found: `' . $package . '`, observing...');
        \Cli\Util::plain('Press CTRL+C to quit.');

        $signature = null;
        $tplp = new \Mysli\Tplp\Tplp([[$package, null]], $this->event);

        while (true) {
            $rsignature = implode('', \Core\FS::dir_signatures($path));
            if ($rsignature !== $signature) {
                $signature = $rsignature;
                \Cli\Util::plain('Tplp: Changes detected, rebuilding...');
                if ($tplp->create_cache($directory)) {
                    \Cli\Util::success('Tplp: OK!');
                } else {
                    \Cli\Util::warn('Tplp: FAILED!');
                }
            }
            sleep(2);
        }
    }
}
