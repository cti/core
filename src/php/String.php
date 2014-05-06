<?php

namespace Cti\Core;

/**
 * String transformation
 * @package Cti\Core
 */
class String
{
    /**
     * convert under_score to UnderScore
     * @param $string
     * @return string
     */
    public static function convertToCamelCase($string)
    {
        foreach(array('_', '-', '.') as $delimiter) {
            if(strstr($string, $delimiter)) {
                return implode('', array_map('ucfirst', explode($delimiter, $string)));
            }
        }
        return ucfirst($string);
    }

    /**
     * convert CamelCase to camel_case string
     * @param $string
     * @return string
     */
    static function camelCaseToUnderScore($string) 
    {
        $start = 0;
        $data = array();
        $lower = strtolower($string);
        for ($k = 1; $k < strlen($string); $k++) {
            if ($lower[$k] != $string[$k]) {
                if ($k != $start) {
                    $data[] = strtolower(substr($string, $start, $k - $start));
                    $start = $k;
                }
            }
        }
        $data[] = strtolower(substr($string, $start, $k - $start));
        return implode('_', $data);
    }

    /**
     * pluralize string
     * @param $string
     * @return string
     */
    public static function pluralize($string)
    {
        $index = strlen($string)-1;
        $last = $string[$index];
        if ($last == 's') {
            return $string . 'es';
        }
        if ($last == 'y') {
            $string[$index] = 'i';

            return $string. 'es';
        }

        return $string.'s';
    }

    /**
     * format size in bytes
     * @param $size
     * @param int $precision
     * @return string
     */
    public static function formatBytes($size, $precision = 2)
    {
        $base = log($size) / log(1024);
        $suffixes = array('', 'k', 'M', 'G', 'T');   
        return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }
}