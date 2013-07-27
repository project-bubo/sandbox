<?php

namespace Utils;

class MultiValues extends \Nette\Object {

    static function unserialize($string, $key = NULL) {
        $array = FALSE;
        if (!empty($string)) {
            // try to unserialize
            $array = @unserialize($string);
        }
        if ($key === NULL) return $array;
            
        // try to find key
        return (!empty($array) && isset($array[$key])) ? $array[$key] : NULL;
    }
    
    static function unserializeArray($values) {
        if (!empty($values)) {
            foreach ($values as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        $u = self::unserialize($v);
                        if ($u !== FALSE)
                            $values[$key][$k] = $u;
                    }
                } else {
                    $u = self::unserialize($value);
                    if ($u !== FALSE)
                        $values[$key] = $u;
                }
            }
        }
        return $values;
    }
    
}
