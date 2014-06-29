<?php

namespace Mysli\I18n\Script;

class I18n
{
    private $config;

    public function __construct(\Mysli\Config\Config $config)
    {
        $this->config = $config;
    }

    /**
     * Print general help.
     */
    public function help_index()
    {
        \Cli\Util::doc(
            'Mysli I18n :: Internationalization',
            'i18n <OPTION> [ARGUMENTS...]',
            [
                'observe <PACKAGE> [DIRECTORY]' =>
                    'Observe package\'s translations for changes, and rebuild directory, if changes are detected.',
            ]
        );

        return true;
    }

    public function action_observe($package = null, $directory = 'i18n')
    {
        $path = pkgpath($package, $directory);
        if (!is_dir($path)) {
            \Cli\Util::warn(
                'I18n: Not a valid package: `' . $package .
                '`, or translations not in directory: `' . $directory . '`.'
            );
            return false;
        }

        \Cli\Util::success('I18n: Package found: `' . $package . '`, observing...');
        \Cli\Util::plain('Press CTRL+C to quit.');

        $signature = null;
        $i18n = new \Mysli\I18n\I18n([[$package, null]], $this->config);

        while (true) {
            $rsignature = implode('', \Core\FS::dir_signatures($path));
            if ($rsignature !== $signature) {
                $signature = $rsignature;
                \Cli\Util::plain('I18n: Changes detected, rebuilding...');
                if ($i18n->create_cache($directory)) {
                    \Cli\Util::success('I18n: OK!');
                } else {
                    \Cli\Util::warn('I18n: FAILED!');
                }
            }
            sleep(2);
        }
    }
}
