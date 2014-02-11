<?php

namespace Mysli\Librarian\Script;

class Librarian
{
    protected $librarian;

    public function __construct(\Mysli\Librarian $librarian)
    {
        $this->librarian = $librarian;
    }

    public function help_index()
    {
        \Cli\Util::doc(
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
        \Cli\Util::doc(
            'Mysli Core :: Libraries Management :: ENABLE',
            'libraries enable <LIBRARY NAME>'
        );
        \Cli\Util::plain('Example: libraries enable mysli/backend');
        return true;
    }
    protected function enable_helper($lib)
    {
        $setup = $this->librarian->construct_setup($lib);
        if (method_exists($setup, 'before_enable') && !$setup->before_enable()) {
            \Cli\Util::error('Setup failed for: ' . $lib);
            return false;
        }
        if (!$this->librarian->enable($lib)) {
            \Cli\Util::error('Failed to enable: ' . $lib);
            return false;
        } else {
            \Cli\Util::success('Enabled: ' . $lib);
            if (method_exists($setup, 'after_enable')) {
                $setup->after_enable();
            }
            return true;
        }
    }
    public function action_enable($lib)
    {
        if (!$lib) {
            return $this->help_enable();
        }
        if ($this->librarian->is_enabled($lib)) {
            \Cli\Util::warn("Library is already enabled: `{$lib}`.");
            return false;
        }
        if (!$this->librarian->resolve($lib, 'disabled')) {
            \Cli\Util::warn("Library not found: `{$lib}`.");
            return false;
        }
        $dependencies = $this->librarian->get_dependencies($lib, true);
        if (!empty($dependencies['missing'])) {
            \Cli\Util::warn('Cannot enable, following libraries are missing: ' .
                print_r($dependencies['missing'], true));
            return false;
        }
        if (count($dependencies['disabled'])) {
            \Cli\Util::plain('The following libraries needs to be enabled: ' .
                print_r($dependencies['disabled'], true));
            if (!\Cli\Util::confirm('Continue and enable required libraries?')) {
                \Cli\Util::plain('Process terminated.');
                return false;
            }
            foreach ($dependencies['disabled'] as $dependency => $version) {
                if (!$this->enable_helper($dependency)) {
                    return false;
                }
            }
        }
        return $this->enable_helper($lib);
    }

    public function help_disable()
    {
        \Cli\Util::doc(
            'Mysli Core :: Libraries Management :: DISABLE',
            'libraries disable <LIBRARY NAME>'
        );
        \Cli\Util::plain('Example: libraries disable mysli/backend');
        return true;
    }
    protected function disable_helper($lib)
    {
        $setup = $this->librarian->construct_setup($lib);
        if (method_exists($setup, 'before_disable') && !$setup->before_disable()) {
            \Cli\Util::error('Setup failed for: ' . $lib);
            return false;
        }
        if (!$this->librarian->disable($lib)) {
            \Cli\Util::error('Failed to disable: ' . $lib);
            return false;
        } else {
            \Cli\Util::success('Disabled: ' . $lib);
            if (method_exists($setup, 'after_enable')) {
                $setup->after_disable();
            }
            return true;
        }
    }
    public function action_disable($lib)
    {
        if (!$lib) {
            return $this->help_disable();
        }
        if (!$this->librarian->is_enabled($lib)) {
            \Cli\Util::warn("Library not enabled: `{$lib}`.");
            return false;
        }
        $dependees = $this->librarian->get_dependees($lib, true);
        if (!empty($dependees)) {
            \Cli\Util::plain('The following libraries depends on the `'. $lib .
                '` and need to be disabled: ' .
                print_r($dependees, true));
            if (!\Cli\Util::confirm('Disable listed libraries?')) {
                \Cli\Util::plain('Process terminated.');
                return false;
            }
            foreach ($dependees as $dependee) {
                if (!$this->disable_helper($dependee)) {
                    return false;
                }
            }
        }
        return $this->disable_helper($lib);
    }

    public function help_list()
    {
        \Cli\Util::doc(
            'Mysli Core :: Libraries Management :: LIST',
            'libraries list <disabled|enabled>'
        );
        \Cli\Util::plain('Example, list all disabled libraries: libraries list disabled');
        return true;
    }
    public function action_list($type = null)
    {
        switch (strtolower($type)) {
            case 'enabled':
                \Cli\Util::nl();
                \Cli\Util::plain(\Core\Arr::readable(
                    $this->librarian->get_enabled(),
                    ' : ', "\n", 2, 2
                ), true);
                break;

            case 'disabled':
                \Cli\Util::nl();
                \Cli\Util::plain(\Core\Arr::readable(
                    $this->librarian->get_disabled(),
                    ' : ', "\n", 2, 2
                ), true);
                break;

            default:
                $this->help_list();
        }
        return true;
    }
}
