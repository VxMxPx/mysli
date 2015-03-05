<?php

namespace mysli\framework\type;

__use(__namespace__, '
    mysli.framework.exception/* -> framework\exception\*
');

class tc {
    static function need_int($input, $min=null, $max=null, $position=0) {
        if (!is_integer($input)) {
            throw new framework\exception\argument(
                "Unexpected type, expected an integer.", 200+$position);
        }
        self::need_size($input, $min, $max, $position);
    }
    static function need_size($input, $min=null, $max=null, $position=0) {
        if ($min !== null && $input < $min) {
            throw new framework\exception\argument(
                "Unexpected value, expected at lest `{$min}`.",
                210+$position);
        }
        if ($max !== null && $input > $max) {
            throw new framework\exception\argument(
                "Unexpected value, expected not more than `{$max}`.", 220);
        }
    }
    static function need_str($input, $position=0) {
        if (!is_string($input)) {
            throw new framework\exception\argument(
                "Unexpected type, expected a string.", 300+$position);
        }
    }
    static function need_str_or_int($input, $position=0) {
        if (!is_integer($input) && !is_string($input)) {
            throw new framework\exception\argument(
                "Unexpected type, expected an integer or a string.",
                400+$position);
        }
    }
    static function need_callback($input, $position=0) {
        if (!is_callable($input)) {
            throw new framework\exception\argument(
                "Unexpected value, needs to be callable!", 500+$position);
        }
    }
}
