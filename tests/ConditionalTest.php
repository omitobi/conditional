<?php

namespace Conditional\Tests;

use Conditional\Conditional;
use PHPUnit\Framework\TestCase;

class ConditionalTest extends TestCase
{

    public function testIfStatement()
    {
        Conditional::if(function () {
            return '1';
        })
            ->then(fn() => dump('Yay'))
            ->else(fn() => dump(['aa']));
    }
}

if (! function_exists('dump')) {
    function dump($expression, $_ = null) {
        var_dump($expression, $_);
    }
}