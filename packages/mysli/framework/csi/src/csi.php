<?php

namespace mysli\framework\csi;

class csi {

    protected $id;

    protected $status = 'none';
    protected $on_validate = null;
    protected $fields = [];

    /**
     * Require unique identifier.
     * The best practice is: namespace/package/[enable|disable]
     * For example: mysli/core/enable
     * @param string $id
     */
    function __construct($id) {
        // Lower case, convert / => _, remove all characters but a-z0-9_
        $this->id = preg_replace(
            '/[^a-z0-9_]/i',
            '',
            str_replace('/', '_', strtolower($id)));
    }
    /**
     * Get object's unique ID. Used for posting.
     * @return string
     */
    function get_id() {
        return $this->id;
    }
    /**
     * Get all assigned values.
     * @return array
     */
    function get_values() {
        $values = [];
        foreach ($this->fields as $id => $properties) {
            if ($properties['value'] !== null) {
                $values[$id] = $properties['value'];
            }
        }
        return $values;
    }
    /**
     * Get all fields' messages (if any).
     * @return array
     */
    function get_messages() {
        $messages = [];
        foreach ($this->fields as $field => $properties) {
            if (!empty($properties['messages'])) {
                $messages[$field] = $properties['messages'];
            }
        }
        return $messages;
    }
    /**
     * Return collection of fields.
     * @return array
     */
    function get_fields() {
        return $this->fields;
    }
    /**
     * Generic field.
     * @param  string   $id
     * @param  string   $type
     * @param  string   $label
     * @param  string   $default
     * @param  callable $callback
     * @return object   $this
     */
    function generic(
        $id, $type, $label='', $options=[], $default='', $callback=false)
    {
        $this->fields[$id] = [
            'id'       => $id,
            'type'     => $type,
            'label'    => $label,
            'options'  => $options,
            'default'  => $default,
            'status'   => null,
            'value'    => null,
            'messages' => [],
            'callback' => $callback
        ];
        return $this;
    }
    /**
     * Output particular text...
     * @return object $this
     */
    function text($text) {
        return $this->generic(count($this->fields), 'text', $text);
    }
    /**
     * Input field. HTML: <input type="text" ...
     * @param  string   $id
     * @param  string   $label
     * @param  string   $default
     * @param  callable $callback
     * @return object   $this
     */
    function input($id, $label='', $default='', $callback=false) {
        return $this->generic($id, 'input', $label, [], $default, $callback);
    }
    /**
     * Input password field. HTML: <input type="password" ...
     * @param  string   $id
     * @param  string   $label
     * @param  callable $callback
     * @return object   $this
     */
    function password($id, $label='', $callback=false) {
        return $this->generic($id, 'password', $label, [], '', $callback);
    }
    /**
     * Input textarea field. HTML: <textarea></ ...
     * @param  string   $id
     * @param  string   $label
     * @param  string   $default
     * @param  callable $callback
     * @return object   $this
     */
    function textarea($id, $label='', $default='', $callback=false) {
        return $this->generic($id, 'textarea', $label, [], $default, $callback);
    }
    /**
     * Input radio field(s).
     * HTML: <input type="radio" name="$id" value="array_key($options)" />
     * @param  string   $id
     * @param  string   $label
     * @param  array    $options
     * @param  string   $default
     * @param  callable $callback
     * @return object   $this
     */
    function radio($id, $label, array $options, $default='', $callback=false) {
        return $this->generic(
            $id, 'radio', $label, $options, $default, $callback);
    }
    /**
     * Input checkbox field.
     * HTML: <input type="checkbox" name="$id" value="array_key($options)" />
     * @param  string   $id
     * @param  string   $label
     * @param  array    $options
     * @param  string   $default
     * @param  callable $callback
     * @return object   $this
     */
    function checkbox($id, $label, array $options, $default='', $callback=false)
    {
        return $this->generic(
            $id, 'checkbox', $label, $options, $default, $callback);
    }
    /**
     * Input hidden field. HTML: <input type="hidden"></ ...
     * @param  string   $id
     * @param  string   $label
     * @param  callable $callback
     * @return object   $this
     */
    function hidden($id, $callback=false) {
        return $this->generic($id, 'hidden', '', [], '', $callback);
    }
    /**
     * Set multiple fields' values.
     * @param array   $values
     * @param boolean $check_unique If extracting data from post, then unique
     * identifier might be added. If this is set true, then check if such
     * identifier exists and accept only fields with it.
     * @return null
     */
    function set_multiple(array $values, $check_unique=false) {
        foreach ($values as $field_id => $value) {
            if ($check_unique) {
                $prefix = 'csi_'. $this->id . '_';
                if (substr($field_id, 0, strlen($prefix)) === $prefix ) {
                    $field_id = substr($field_id, strlen($prefix));
                } else continue;
            }
            $this->set($field_id, $value);
        }
    }
    /**
     * Set one value for field.
     * @param string $field
     * @param mixed  $value
     * @return null
     */
    function set($field, $value) {
        if (array_key_exists($field, $this->fields)) {
            $this->fields[$field]['value'] = $value;
        }
    }
    /**
     * Get field's value.
     * This won't function properly if validate wasn't run.
     * @param  string $field_id
     * @param  mixed  $default
     * @return mixed
     */
    function get($field_id, $default=null) {
        if (array_key_exists($field_id, $this->fields)) {
            return $this->fields[$field_id]['value'];
        } else return $default;
    }
    /**
     * Set validate callback.
     * @param  callback $callback
     * @return void
     */
    function on_validate($callback) {
        $this->on_validate = $callback;
    }
    /**
     * Validate fields. Require values ($_POST for example), alternative to set
     * values is $this->set().
     * @param  array  $values
     * @return boolean
     */
    function validate(array $values=null) {
        // Parse values!
        if ($values) {
            $this->set_multiple($values, true);
        }

        $statuses = [];

        if (is_callable($this->on_validate)) {
            $status = call_user_func_array(
                                        $this->on_validate, [&$this->fields]);
            $statuses[] = $status;
            // if     ($status === true)  $this->status = 'success';
            // elseif ($status === false) $this->status = 'failed';
        }

        // Collection of statuses
        foreach ($this->fields as $field => &$properties) {
            // Reset field's messages
            $properties['messages'] = [];

            if (is_callable($properties['callback'])) {
                // Call costume function
                $status = call_user_func_array($properties['callback'],
                                                [&$properties]);
                // Set field's status
                $properties['status'] = $status;
                // interrupted
                if ($status === -1) {
                    $this->status = 'interrupted';
                    return false;
                }
                // Add status to all statuses array
                $statuses[] = $status;
                // Check if we've got -1
                // if ($status === -1) {
                //     $this->status = 'interrupted';
                //     return false;
                // }
            } else {
                $properties['status'] = true;
                $statuses[] = true;
            }
        }

        if (!empty($statuses)) {
            if (in_array(false, $statuses)) {
                $this->status = 'failed';
            } elseif (in_array(true, $statuses)) {
                $this->status = 'success';
            }
        } else {
            // Nothing to validate, success!
            $this->status = 'success';
        }

        return ($this->status === 'success');
    }
    /**
     * Return current status. Possible values:
     * success, failed, none, interrupted
     * @return string
     */
    function status() {
        return $this->status;
    }
}
