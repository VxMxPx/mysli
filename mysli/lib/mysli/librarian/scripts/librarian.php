<?php

namespace Mysli\Librarian\Script;

class Librarian
{
    protected $core;

    public function __construct(array $config = [], array $dependencies = [])
    {
        $this->core = $dependencies['core'];
    }

    public function help_index()
    {
        \Dot\Util::doc(
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
        \Dot\Util::doc(
            'Mysli Core :: Libraries Management :: ENABLE',
            'libraries enable <LIBRARY NAME>'
        );
        \Dot\Util::plain('Example: libraries enable mysli/backend');
        return true;
    }
    protected function enable_helper($lib)
    {
        $setup = $this->core->librarian->construct_setup($lib);
        if (method_exists($setup, 'before_enable') && !$setup->before_enable()) {
            \Dot\Util::error('Setup failed for: ' . $lib);
            return false;
        }
        if (!$this->core->librarian->enable($lib)) {
            \Dot\Util::error('Failed to enable: ' . $lib);
            return false;
        } else {
            \Dot\Util::success('Enabled: ' . $lib);
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
        if ($this->core->librarian->is_enabled($lib)) {
            \Dot\Util::warn("Library is already enabled: `{$lib}`.");
            return false;
        }
        if (!$this->core->librarian->resolve($lib, 'disabled')) {
            \Dot\Util::warn("Library not found: `{$lib}`.");
            return false;
        }
        $dependencies = $this->core->librarian->get_dependencies($lib, true);
        if (!empty($dependencies['missing'])) {
            \Dot\Util::warn('Cannot enable, following libraries are missing: ' .
                print_r($dependencies['missing'], true));
            return false;
        }
        if (count($dependencies['disabled'])) {
            \Dot\Util::plain('The following dependencies needs to be enabled: ' .
                print_r($dependencies['disabled'], true));
            if (!\Dot\Util::confirm('Continue and enable disabled dependencies?')) {
                \Dot\Util::plain('Process terminated.');
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
        \Dot\Util::doc(
            'Mysli Core :: Libraries Management :: DISABLE',
            'libraries disable <LIBRARY NAME>'
        );
        \Dot\Util::plain('Example: libraries disable mysli/backend');
        return true;
    }
    protected function disable_helper($lib)
    {
        $setup = $this->core->librarian->construct_setup($lib);
        if (method_exists($setup, 'before_disable') && !$setup->before_disable()) {
            \Dot\Util::error('Setup failed for: ' . $lib);
            return false;
        }
        if (!$this->core->librarian->disable($lib)) {
            \Dot\Util::error('Failed to disable: ' . $lib);
            return false;
        } else {
            \Dot\Util::success('Disabled: ' . $lib);
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
        if (!$this->core->librarian->is_enabled($lib)) {
            \Dot\Util::warn("Library not enabled: `{$lib}`.");
            return false;
        }
        $dependees = $this->core->librarian->get_dependees($lib, true);
        if (!empty($dependees)) {
            \Dot\Util::plain('The following libraried depends on the `'. $lib .
                '` and need to be disabled: ' .
                print_r($dependees, true));
            if (!\Dot\Util::confirm('Disable listed libraries?')) {
                \Dot\Util::plain('Process terminated.');
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
        \Dot\Util::doc(
            'Mysli Core :: Libraries Management :: LIST',
            'libraries list <disabled|enabled>'
        );
        \Dot\Util::plain('Example, list all disabled libraries: libraries list disabled');
        return true;
    }
    public function action_list($type)
    {
        switch (strtolower($type)) {
            case 'enabled':
                \Dot\Util::plain(print_r($this->core->librarian->get_enabled()), true);
                break;

            case 'disabled':
                \Dot\Util::plain(print_r($this->core->librarian->get_disabled()), true);
                break;

            default:
                $this->help_list();
        }
        return true;
    }
}