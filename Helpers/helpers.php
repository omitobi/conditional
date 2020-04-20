<?php

use Conditional\Conditional;

if (!function_exists('conditional')) {
    /**
     * New up an instance of readied Conditional
     *
     * @param $condition
     * @param mixed $then
     * @param mixed $else
     * @return Conditional
     */
    function conditional($condition, $then = null, $else = null)
    {
        $conditional = Conditional::if($condition);

        if (func_num_args() === 2) {
            $conditional->then($then);
        }

        if (func_num_args() === 3) {
            $conditional->then($then)->else($else);
        }

        return $conditional;
    }
}