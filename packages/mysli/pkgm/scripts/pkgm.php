<?php

namespace Mysli\Pkgm\Script;

class Pkgm
{
    protected $pkgm;

    public function __construct(\Mysli\Pkgm $pkgm)
    {
        $this->pkgm = $pkgm;
    }

    public function help_index()
    {
        \Cli\Util::doc(
            'Mysli Core :: Packages Management',
            'packages <OPTION> [ARGUMENTS...]',
            [
                'enable'  => 'Will enable particular package.',
                'disable' => 'Will disable particular package.',
                'list'    => 'Will list enabled / disabled packages.'
            ]
        );

        return true;
    }

    public function help_enable()
    {
        \Cli\Util::doc(
            'Mysli Core :: Packages Management :: ENABLE',
            'packages enable <PACKAGE NAME>'
        );
        \Cli\Util::plain('Example: packages enable mysli/config');

        return true;
    }

    protected function enable_helper($pkg)
    {
        $setup = $this->pkgm->construct_setup($pkg);

        if (method_exists($setup, 'before_enable') && !$setup->before_enable()) {
            \Cli\Util::error('Setup failed for: ' . $pkg);
            return false;
        }

        if (!$this->pkgm->enable($pkg)) {
            \Cli\Util::error('Failed to enable: ' . $pkg);
            return false;
        } else {
            \Cli\Util::success('Enabled: ' . $pkg);
            if (method_exists($setup, 'after_enable')) {
                $setup->after_enable();
            }
            return true;
        }
    }

    public function action_enable($pkg)
    {
        if (!$pkg) {
            return $this->help_enable();
        }

        if ($this->pkgm->is_enabled($pkg)) {
            \Cli\Util::warn("Package is already enabled: `{$pkg}`.");
            return false;
        }

        if (!$this->pkgm->resolve($pkg, 'disabled')) {
            \Cli\Util::warn("Package not found: `{$pkg}`.");
            return false;
        }

        $dependencies = $this->pkgm->get_dependencies($pkg, true);

        if (!empty($dependencies['missing'])) {
            \Cli\Util::warn(
                "Cannot enable, following packages are missing: \n\n" .
                \Core\Arr::readable($dependencies['missing'], 2) . "\n"
            );
            return false;
        }

        if (count($dependencies['disabled'])) {
            \Cli\Util::plain(
                "The following packages needs to be enabled: \n\n" .
                \Core\Arr::readable($dependencies['disabled'], 2) . "\n"
            );

            if (!\Cli\Util::confirm('Continue and enable required packages?')) {
                \Cli\Util::plain('Process terminated.');
                return false;
            }

            foreach ($dependencies['disabled'] as $dependency => $version) {
                if (!$this->enable_helper($dependency)) {
                    return false;
                }
            }
        }

        return $this->enable_helper($pkg);
    }

    public function help_disable()
    {
        \Cli\Util::doc(
            'Mysli Core :: Packages Management :: DISABLE',
            'packages disable <PACKAGE NAME>'
        );
        \Cli\Util::plain('Example: packages disable mysli/config');

        return true;
    }

    protected function disable_helper($pkg)
    {
        $setup = $this->pkgm->construct_setup($pkg);

        if (method_exists($setup, 'before_disable') && !$setup->before_disable()) {
            \Cli\Util::error('Setup failed for: ' . $pkg);
            return false;
        }

        if (!$this->pkgm->disable($pkg)) {
            \Cli\Util::error('Failed to disable: ' . $pkg);
            return false;
        } else {
            \Cli\Util::success('Disabled: ' . $pkg);
            if (method_exists($setup, 'after_disable')) {
                $setup->after_disable();
            }
            return true;
        }
    }

    public function action_disable($pkg)
    {
        if (!$pkg) {
            return $this->help_disable();
        }

        if (!$this->pkgm->is_enabled($pkg)) {
            \Cli\Util::warn("Package not enabled: `{$pkg}`.");
            return false;
        }

        $dependees = $this->pkgm->get_dependees($pkg, true);

        if (!empty($dependees)) {
            \Cli\Util::plain(
                "The following packages depends on the `{$pkg}` and need to be disabled:\n\n" .
                \Core\Arr::readable($dependees, 2) . "\n"
            );

            if (!\Cli\Util::confirm('Disable listed packages?')) {
                \Cli\Util::plain('Process terminated.');
                return false;
            }

            foreach ($dependees as $dependee) {
                if (!$this->disable_helper($dependee)) {
                    return false;
                }
            }
        }

        return $this->disable_helper($pkg);
    }

    public function help_list()
    {
        \Cli\Util::doc(
            'Mysli Core :: Packages Management :: LIST',
            'packages list <disabled|enabled>'
        );
        \Cli\Util::plain('Example, list all disabled packages: packages list disabled');

        return true;
    }

    public function action_list($type = null)
    {
        switch (strtolower($type)) {
            case 'enabled':
                \Cli\Util::nl();
                \Cli\Util::plain(
                    \Core\Arr::readable($this->pkgm->get_enabled(), 2),
                    true
                );
                break;

            case 'disabled':
                \Cli\Util::nl();
                \Cli\Util::plain(
                    \Core\Arr::readable($this->pkgm->get_disabled(), 2),
                    true
                );
                break;

            default:
                $this->help_list();
        }

        return true;
    }
}
