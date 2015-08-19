# Datetime

Allows manipulations of date and time.
This is a flexible class with a couple of static shortcuts, and possibility
to be instantiated.

A native \DateTime object can be used, rather than this class, which
is here only to simplify things a little bit. For example, there's no need to
construct \TimeZone object, to change timezone, etc...

## Usage

    // New instance, with current UTC datetime
    $datetime = new datetime();
    $datetime->format(datetime::iso8601); // == $datetime->format('c');

Timezone can be easily changed at any time:

    $datetime->set_timezone('Europe/Ljubljana');

The current date/time can be changed...

    $datetime->set_datetime(datetime::now());

...or modified (will return a Unix timestamp):

    $datetime->modify('+1 day');

To get datetime difference, `diff` method can be called (returns \DateInterval):

    $datetime->diff($a_valid_datetime_string);
