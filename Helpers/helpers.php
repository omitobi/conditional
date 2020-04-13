<?php

use Conditional\Conditional;

if (!function_exists('conditional')) {
    /**
     * New up an instance of readied Conditional
     *
     * @param $condition
     * @return Conditional
     */
    function conditional($condition)
    {
        return Conditional::if($condition);
    }
}