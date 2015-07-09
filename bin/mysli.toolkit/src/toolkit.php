<?php

/**
 * A toolkit core class. Offers some common methods, like shutdown and panic.
 */
namespace mysli\toolkit; class toolkit
{
    /**
     * Called when a normal shutdown happened.
     * This will load shutdown, and trigger standard shutdown event.
     * --
     * @param integer $code
     *        Code to be used for exit (and when even is triggered).
     *        If you're unsure leave it to be the default value which is `0`.
     * --
     * @event toolkit::shutdown ( integer $code )
     */
    static function shutdown($code=0)
    {
        log::info('System is about to shutdown. Bye! :)', __CLASS__);
        event::trigger("toolkit::shutdown", [$code]);

        if (!is_cli())
            echo log_to_html( log::get() );

        exit($code);
    }

    /**
     * Emergency shutdown, call this to imediately stop the system.
     * This will write the panic log, trigger panic event and then exit.
     * --
     * @event toolkit::panic ()
     */
    static function panic()
    {
        log::panic('System will be stopped now. :(', __CLASS__);
        event::trigger("toolkit::panic");

        if (!is_cli())
            echo log_to_html( log::get() );

        exit(11);
    }
}
