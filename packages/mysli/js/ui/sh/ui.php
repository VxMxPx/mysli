<?php

namespace mysli\js\ui\sh\ui;

__use(__namespace__, '
    mysli.util.tplp
    mysli.framework.event
    mysli.framework.cli/output,param  -> cout,cparam
');


function __init(array $args=[])
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
        enable_dev();
    }
    elseif ($values['disable'])
    {
        disable_dev();
    }
    else
    {
        cout::line("Enter --help for help");
    }
}

function enable_dev()
{
    event::register(
        'mysli.web.web:route<*><mwu-developer*>',
        'mysli\\js\\ui::developer'
    );
    cout::success(
        'Enabled. Use: '.
        'http://localhost:8000/mwu-developer to access developer mode.'
    );
}
function disable_dev()
{
    event::unregister(
        'mysli.web.web:route<*>mwu-developer*>',
        'mysli\\js\\ui::developer'
    );
    cout::success('Disabled.');
}