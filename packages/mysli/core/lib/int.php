<?php

namespace Mysli\Core\Lib;

class Int
{
    /**
     * Compare version with requirements. E.g. $version 1.0, $required '>= 1.0'
     * --
     * @param  float  $version  The actual version. (0.1)
     * @param  string $required The required version. ('>= 0.2')
     * --
     * @return boolean
     */
    public static function compare_versions($version, $required)
    {
        $version  = floatval($version);
        $required = explode(' ', $required);
        $operator = trim($required[0]);
        $required = floatval(trim($required[1]));

        switch ($operator) {
            case '>=' : return $version >=  $required;
            case '<=' : return $version <=  $required;
            case '='  : return $version === $required;
            case '<'  : return $version  <  $required;
            case '>'  : return $version  >  $required;
            default   : return false;
        }
    }
}
