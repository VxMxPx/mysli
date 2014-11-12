<?php

namespace mysli\web\ui\script;

__use(__namespace__, '
    mysli/util/tplp
    mysli/framework/event
    mysli/framework/cli/{output,param} AS {cout,cparam}
');

class ui {
    static function run(array $args=[]) {
        $params = new cparam('Mysli Web UI', $args);
        $params->command = 'ui';
        $params->add(
            '--enable/-e',
            ['type'    => 'bool',
             'exclude' => ['disable'],
             'help'    => 'Enable access to developer mode.']);
        $params->add(
            '--disable/-d',
            ['type'    => 'bool',
             'help'    => 'Disable access to developer mode.']);

        $params->parse();

        if (!$params->is_valid()) {
            cout::line($params->messages());
            return;
        }

        $values = $params->values();

        if ($values['enable']) {
            self::enable_dev();
        } elseif ($values['disable']) {
            self::disable_dev();
        } else {
            cout::line("Enter --help for help");
        }
    }

    private static function enable_dev() {
        event::register('mysli/web/web:route<*><mwu-developer*>',
                        'mysli\\web\\ui::developer');

        cout::success(
            'Enabled. Use: '.
            'http://localhost:8000/mwu-developer to access developer mode.');
    }
    private static function disable_dev() {
        tplp::remove_cache('mysli/web/ui');
        event::unregister(
            'mysli/web/web:route<*>mwu-developer*>',
            'mysli\\web\\ui::developer');

        cout::success('Disabled.');
    }
}
