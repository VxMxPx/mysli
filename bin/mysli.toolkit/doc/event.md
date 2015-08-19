# Event

Allows you you to handle system events.

To wait for an events, use:

    event::on('event::action', function ($param, $param2) {});

To trigger an event, use:

    event::trigger('event::action', [$param, $param2]);

Priority can be set with:

    event::on('event::action', 'vend.pkg.cls::mtd', event::priority_high);

There are only two priorities, `event::priority_high` and
`event::priority_low`, the first will push function to the top of
the waiting list, the other to the bottom.

Events can be permanenty added the the list, meaning, you don't need to use
`on` method on each run, rather function will be called each time event is
triggered regardless if you've used `on` method:

    event::register('event::action', 'vend.pkg.cls::mtd');

You can register multiple handlers:

    event::register([
        'event::action' => 'vend.pkg.cls::mtd',
        'event::different' => 'vend.pkg.cls::diff_mtd'
    ]);

To stop observing events, use `unregister`,
in same format as you've used `register`:

    event::unregister('event::action', 'vend.pkg.cls::mtd');
