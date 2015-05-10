<?php

namespace mysli\js\ui\sh;

__use(__namespace__, '
    ./__init
    mysli.util.tplp
    mysli.framework.event
    mysli.framework.cli/output,param  -> cout,cparam
');

class ui
{
    static function __init(array $args=[])
    {
        $params = new cparam('Mysli JS UI', $args);
        $params->command = 'ui';
        $params->add('--enable/-e', [
            'type'    => 'bool',
            'exclude' => ['disable'],
            'help'    => 'Enable access to developer mode.'
        ]);
        $params->add('--disable/-d', [
            'type' => 'bool',
            'help' => 'Disable access to developer mode.'
        ]);

        $params->parse();

        if (!$params->is_valid())
        {
            cout::line($params->messages());
            return;
        }

        $values = $params->values();

        if ($values['enable'])
        {
            self::enable_dev();
        }
        elseif ($values['disable'])
        {
            self::disable_dev();
        }
        else
        {
            cout::line("Enter --help for help");
        }
    }

    static function enable_dev()
    {
        event::register(__init::$events);
        cout::success(
            'Enabled. Use: '.
            'http://localhost:8000/mwu-developer to access developer mode.'
        );
    }
    static function disable_dev()
    {
        event::unregister(__init::$events);
        cout::success('Disabled.');
    }
}
