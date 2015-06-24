<?php

namespace mysli\toolkit; class log
{
    const debug   = 'debug';
    const info    = 'info';
    const notice  = 'notice';
    const warning = 'warning';
    const error   = 'error';
    const panic   = 'panic';

    /**
     * All messages added to the log.
     * --
     * @var array
     */
    static private $messages = [];

    /**
     * Debug information.
     * --
     * @param  string $message
     * @param  array  $context
     */
    static function debug($message, array $context=[])
    {
        self::add(self::debug, $message, $context);
    }

    /**
     * General information.
     * --
     * @param  string $message
     * @param  array  $context
     */
    static function info($message, array $context=[])
    {
        self::add(self::info, $message, $context);
    }

    /**
     * Significant information.
     * --
     * @param  string $message
     * @param  array  $context
     */
    static function notice($message, array $context=[])
    {
        self::add(self::notice, $message, $context);
    }

    /**
     * Warning!
     * --
     * @param  string $message
     * @param  array  $context
     */
    static function warn($message, array $context=[])
    {
        self::add(self::warning, $message, $context);
    }

    /**
     * Errors of any type.
     * --
     * @param  string $message
     * @param  array  $context
     */
    static function error($message, array $context=[])
    {
        self::add(self::error, $message, $context);
    }

    /**
     * Fatal or any other serious full-stop situation.
     * --
     * @param  string $message
     * @param  array  $context
     */
    static function panic($message, array $context=[])
    {
        self::add(self::panic, $message, $context);
    }

    /**
     * Add log message of a costume type.
     * --
     * @param string $type
     * @param string $message
     * @param array  $context
     */
    static function add($type, $message, array $context=[])
    {
        if (!empty($context))
        {
            foreach ($context as $key => $value)
            {
                if (is_a($value, 'Exception'))
                {
                    $message .= "\n\nStack trace:".$value->getTraceAsString();
                    $value = $value->getMessage();
                }

                $message = str_replace('{'.$key.'}', $value, $message);
            }
        }

        self::$messages[] = [
            'type'      => $type,
            'timestamp' => date('c'),
            'message'   => $message
        ];
    }

    /**
     * Get all log messages, or all of particular type.
     * This is limited to messages added in this session.
     * --
     * @param  string $type
     * --
     * @return array
     */
    static function get($type=null)
    {
        if (!$type)
        {
            return self::$messages;
        }
        else
        {
            $messages = [];

            foreach (self::$messages as $message)
            {
                if ($message['type'] === $type)
                    $messages[] = $message;
            }

            return $messages;
        }
    }
}
