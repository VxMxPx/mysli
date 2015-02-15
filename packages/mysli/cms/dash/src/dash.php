<?php

namespace mysli\cms\dash;

__use(__namespace__, '
    mysli.framework.type/arr
');

class dash {

    private $data = [];

    const msg_error   = 'error';
    const msg_info    = 'info';
    const msg_warn    = 'warn';
    const msg_success = 'success';

    /**
     * Return new instance of self, with data added.
     * @param  array  $data
     * @return mysli\cms\dash\dash
     */
    static function response(array $data) {
        return (new self)->add($data);
    }
    /**
     * Return new instance of self, with message added.
     * @param  string $message
     * @param  string $type
     * @return mysli\cms\dash\dash
     */
    static function response_message($message, $type=self::msg_info) {
        return (new self)->message($message, $type);
    }

    /**
     * Add data.
     * @param mixed $key  string (key) or array (merge)
     * @param array $data null if key is array
     * @return mysli\cms\dash\dash
     */
    function add($key, $data=null) {
        if (is_array($key)) {
            $this->data = arr::merge($this->data, $key);
        } else {
            $this->data[$key] = $data;
        }
        return $this;
    }
    /**
     * Add message.
     * @param  string $message
     * @param  string $type
     * @return mysli\cms\dash\dash
     */
    function message($message, $type=self::msg_info) {
        if (!isset($this->data['messages'])) {
            $this->data['messages'] = [];
        }
        if (!isset($this->data['messages'][$type])) {
            $this->data['messages'][$type] = [];
        }
        $this->data['messages'][$type][] = $message;
        return $this;
    }
    /**
     * Return all data as an array
     * @return array
     */
    function as_array() {
        return $this->data;
    }
}
