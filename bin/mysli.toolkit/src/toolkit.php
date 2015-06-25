<?php

/**
 * A toolkit core class. Offers some common methods, like shutdown and panic.
 */
namespace mysli\toolkit; class toolkit
{
    /**
     * Called when a normal shutdown happened.
     * This will load shutdown, and trigger standard shutdown event.
     */
    static function shutdown()
    {
        log::info('System is about to shutdown. Bye! :)', __CLASS__);
        event::trigger("toolkit::shutdown");

        echo log_to_html( log::get() );
    }

    /**
     * Emergency shutdown, call this to imediately stop the system.
     * This will write the panic log, trigger panic event and then exit.
     */
    static function panic()
    {
        log::panic('System will be stopped now. :(', __CLASS__);
        event::trigger("toolkit::panic");

        echo log_to_html( log::get() );
        exit(11);
    }
}
