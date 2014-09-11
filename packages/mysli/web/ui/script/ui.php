<?php

namespace Mysli\Ui\Script;

class Ui
{
    private $event;
    private $tplp;

    public function __construct(\Mysli\Tplp\Tplp $tplp, \Mysli\Event\Event $event)
    {
        $this->event = $event;
        $this->tplp = $tplp;
    }

    /**
     * Print general help.
     */
    public function help_index()
    {
        \Cli\Util::doc(
            'Mysli UI',
            'ui examples <enable|disable>',
            [
                'examples <enable|disable>' =>
                    'Enable or disable access to the examples. For debugging.',
            ]
        );

        return true;
    }

    /**
     * Enable/disable examples.
     * @param  string $state enable|disable
     * @return null
     */
    public function action_examples($state = null)
    {
        if ($state === 'enable') {
            $this->tplp->create_cache();
            $this->event->register('mysli/web/route:*<mysli-ui-examples*>', 'mysli/ui->examples');
            \Cli\Util::success('Enabled. Use: http://localhost/mysli-ui-examples to access examples.');
        }
        else if ($state === 'disable') {
            $this->tplp->remove_cache();
            $this->event->unregister('mysli/web/route:*<mysli-ui-examples*>', 'mysli/ui->examples');
            \Cli\Util::success('Disabled.');
        }
        else {
            $this->help_index();
        }
    }
}
