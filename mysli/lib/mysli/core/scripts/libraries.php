<?php

namespace Mysli\Core\Script;

class Libraries
{
    public function help_index()
    {
        \DotUtil::doc(
            'Mysli Core :: Libraries Management',
            'libraries <OPTION> [ARGUMENTS...]',
            [
                'enable'  => 'Will enable particular library.',
                'disable' => 'Will disable particular library.',
                'list'    => 'Will list enabled / disabled libraries.'
            ]
        );
        return true;
    }

    public function help_enable()
    {
        \DotUtil::doc(
            'Mysli Core :: Libraries Management :: ENABLE',
            'libraries enable <LIBRARY NAME>'
        );
        \DotUtil::plain('Example: libraries enable mysli/backend');
        return true;
    }
    public function action_enable($lib)
    {
        if (!$lib) {
            return $this->help_enable();
        }
        if (\Librarian::is_enabled($lib)) {
            \DotUtil::warn("Library is already enabled: `{$lib}`.");
            return false;
        }
        if (!\Librarian::resolve($lib, 'disabled')) {
            \DotUtil::warn("Library not found: `{$lib}`.");
            return false;
        }
        $dependencies = \Librarian::get_dependencies($lib, true);
        if (!empty($dependencies['missing'])) {
            \DotUtil::warn('Cannot enable, following libraries are missing: ' .
                print_r($dependencies['missing'], true));
            return false;
        }
        if (count($dependencies['disabled'])) {
            \DotUtil::plain('The following dependencies needs to be enabled: ' .
                print_r($dependencies['disabled'], true));
            if (!\DotUtil::confirm('Continue and enable disabled dependencies?')) {
                \DotUtil::plain('Process terminated.');
                return false;
            }
            foreach ($dependencies['disabled'] as $dependency => $version) {
                if (!\Librarian::enable($dependency)) {
                    \DotUtil::error('Failed to enable: ' . $dependency);
                    return false;
                } else {
                    \DotUtil::success('Enabled: ' . $dependency);
                }
            }
        }
        if (!\Librarian::enable($lib)) {
            \DotUtil::error('Failed to enable: ' . $lib);
            return false;
        } else {
            \DotUtil::success('Enabled: ' . $lib);
        }
    }

    public function help_disable()
    {
        \DotUtil::doc(
            'Mysli Core :: Libraries Management :: DISABLE',
            'libraries disable <LIBRARY NAME>'
        );
        \DotUtil::plain('Example: libraries disable mysli/backend');
        return true;
    }

    public function action_disable($lib)
    {
        if (!$lib) {
            return $this->help_disable();
        }
        if (!\Librarian::is_enabled($lib)) {
            \DotUtil::warn("Library not enabled: `{$lib}`.");
            return false;
        }
        $dependees = \Librarian::get_dependees($lib, true);
        if (!empty($dependees)) {
            \DotUtil::plain('The following libraried depends on the `'. $lib .
                '` and need to be disabled: ' .
                print_r($dependees, true));
            if (!\DotUtil::confirm('Disable listed libraries?')) {
                \DotUtil::plain('Process terminated.');
                return false;
            }
            foreach ($dependees as $dependee) {
                if (!\Librarian::disable($dependee)) {
                    \DotUtil::error('Failed to disable: ' . $dependee);
                    return false;
                } else {
                    \DotUtil::success('Disabled: ' . $dependee);
                }
            }
        }
        if (!\Librarian::disable($lib)) {
            \DotUtil::error('Failed to disable: ' . $lib);
            return false;
        } else {
            \DotUtil::success('Disabled: ' . $lib);
        }
    }

    public function help_list()
    {
        \DotUtil::doc(
            'Mysli Core :: Libraries Management :: LIST',
            'libraries list <disabled|enabled>'
        );
        \DotUtil::plain('Example, list all disabled libraries: libraries list disabled');
        return true;
    }
    public function action_list($type)
    {
        switch (strtolower($type)) {
            case 'enabled':
                \DotUtil::plain(print_r(\Librarian::get_enabled()), true);
                break;

            case 'disabled':
                \DotUtil::plain(print_r(\Librarian::get_disabled()), true);
                break;

            default:
                $this->help_list();
        }
        return true;
    }
}
