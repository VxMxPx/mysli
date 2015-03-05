<?php

namespace mysli\util\datetime;

__use(__namespace__, '
    mysli.framework.exception/* -> framework\exception\*
');

class datetime {

    const timestamp = 'datetime::timestamp';
    const timezone  = 'datetime::timezone';
    const time      = 'H:i:s';
    const day       = 'd';
    const month     = 'm';
    const year      = 'Y';
    const sort      = 'YmdHis';
    const atom      = 'Y-m-d\TH:i:sP';
    const cookie    = 'l, d-M-Y H:i:s T';
    const iso8601   = 'Y-m-d\TH:i:sO';
    const rfc822    = 'D, d M y H:i:s O';
    const rfc850    = 'l, d-M-y H:i:s T';
    const rfc1036   = 'D, d M y H:i:s O';
    const rfc1123   = 'D, d M Y H:i:s O';
    const rfc2822   = 'D, d M Y H:i:s O';
    const rfc3339   = 'Y-m-d\TH:i:sP';
    const rss       = 'D, d M Y H:i:s O';
    const w3c       = 'Y-m-d\TH:i:sP';

    protected $datetime;

    /**
     * Instance of datetime object
     * @param mixed  $datetime
     * @param string $timezone
     */
    function __construct($datetime=null, $timezone=null) {
        if (is_null($timezone)) {
            $timezone = self::get_default_timezone();
        }

        // null, generate date
        if (!$datetime) {
            $datetime = gmdate('Y-m-d H:i:s');
        }
        // probably timestamp
        if (is_numeric($datetime)) {
            $datetime = gmdate('Y-m-d H:i:s', $datetime);
        }

        if (is_string($datetime)) {
            $this->datetime = new \DateTime($datetime,
                new \DateTimeZone($timezone));
        } elseif (is_object($datetime)) {
            if ($datetime instanceof \DateTime) {
                $this->datetime = $datetime;
            } elseif ($datetime instanceof self) {
                $this->datetime = new \DateTime(
                    $datetime->format('Y-m-d H:i:s'),
                    new \DateTimeZone($datetime->format(self::timezone)));
            } else {
                throw new framework\exception\argument(
                    "Invalid \$datetime object type.", 1);
            }
        } else {
            throw new framework\exception\argument(
                "Invalid \$datetime object type.", 2);
        }
    }
    /**
     * Set new date/time.
     * @param  mixed $datetime
     * @return null
     */
    function set_datetime($datetime) {
        if (is_object($datetime)) {
            if ($datetime instanceof \DateTime) {
                $datetime = $datetime->getTimestamp();
            } elseif ($datetime instanceof self) {
                $datetime = $datetime->format(self::timestamp);
            } else {
                throw new framework\exception\argument(
                    "Invalid \$datetime object type.", 1);
            }
        }
        if (!is_numeric($datetime)) {
            $datetime = strtotime($datetime);
        }
        $this->datetime->setTimestamp($datetime);
    }
    /**
     * Set new timezone.
     * @param  string $timezone
     * @return null
     */
    function set_timezone($timezone) {
        $this->datetime->setTimezone(new \DateTimeZone($timezone));
    }
    /**
     * Format date/time.
     * @param  string $format
     * @param  string $datetime
     * @return string
     */
    function format($format) {
        if ($format === self::timezone) {
            return $this->datetime->getTimezone()->getName();
        }
        if ($format === self::timestamp) {
            return $this->datetime->getTimestamp();
        }
        return $this->datetime->format($format);
    }
    /**
     * Parse about any English textual date/time description
     * into a Unix timestamp.
     * @param  string $modify
     * @return integer
     */
    function modify($modify) {
        return new self($this->datetime->modify($modify));
    }
    /**
     * Return difference between two DateTimeInterface objects.
     * @param  string $datetime
     * @return DateInterval
     */
    function diff($datetime) {
        return date_diff($this->datetime, new \DateTime($datetime));
    }

    // Static functions

    /**
     * Set the default timezone used by all date/time functions.
     * @param  string  $timezone
     * @return null
     */
    static function set_default_timezone($timezone) {
        date_default_timezone_set($timezone);
    }
    /**
     * Get the default timezone used by all date/time functions.
     * @return string
     */
    static function get_default_timezone() {
        return date_default_timezone_get();
    }
    /**
     * Return Unix timestamp for current date/time! Always UTC.
     * @return integer
     */
    static function now() {
        return time();
    }
    /**
     * Format date/time.
     * @param  string $format
     * @param  mixed  $datetime
     * @return string
     */
    static function f($format, $datetime=null) {
        return (new self($datetime))->format($format);
    }
}
