<?php

namespace mysli\web\ui\script;

__use(__namespace__, [
    'mysli/framework' => [
        'cli/{output,param}' => 'cout,cparam',
    ]
]);

class ui {
    static function run(array $args=[]) {
        $params = new cparam('Mysli UI', $args);
        $params->command = 'ui';
        $params->add(
            '--enable/-e',
            ['type'    => 'bool',
             'exclude' => ['disable'],
             'help'    => 'Enable access to examples. For debugging.']);
        $params->add(
            '--disable/-d',
            ['type'    => 'bool',
             'help'    => 'Disable access to examples.']);

        $params->parse();

        if (!$params->is_valid()) {
            cout::line($params->messages());
            return;
        }

        $values = $params->values();

        if ($values['enable']) {
            self::enable_examples();
        } else {
            self::disable_examples();
        }
    }

    private static function enable_examples() {
        event::register('mysli/web/web:route<*><mysli-ui-examples*>',
                        'mysli\\web\\ui::examples');
        cout::success('Enabled. Use: '.
                    'http://localhost/mysli-ui-examples to access examples.');
    }
    private static function disable_examples() {
        tplp::remove_cache('mysli/web/ui');
        event::unregister('mysli/web/web:route<*><mysli-ui-examples*>',
                            'mysli\\web\\ui::examples');
        cout::success('Disabled.');
    }
}
