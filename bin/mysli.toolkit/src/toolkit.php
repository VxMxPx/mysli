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
        log::info('System going for shutdown.');
        event::trigger("toolkit::shutdown");

        dump(log::get());
    }

    /**
     * Emergency shutdown, call this to imediately stop the system.
     * This will write the panic log, trigger panic event and then exit.
     */
    static function panic()
    {
        log::panic('Panic called! System will be stopped immediately.');
        event::trigger("toolkit::panic");
        exit(11);
    }
}
