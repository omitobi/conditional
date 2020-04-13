<?php

namespace Conditional\Tests;

use Closure;
use Conditional\Conditional;
use PHPUnit\Framework\TestCase;

class ConditionalTest extends TestCase
{
    public function testExecutionFollowsConditions()
    {
        $firstResponse = 1;
        $secondResponse = 2;

        Conditional::if($firstResponse === $secondResponse)
            ->then(fn() => $this->assertEquals($firstResponse, $secondResponse))
            ->else(fn() => $this->assertNotEquals($firstResponse, $secondResponse));
    }

    public function testThatValueIsReceived()
    {
        $firstResponse = 1;
        $secondResponse = 2;

        $value = rand(1,2);

        $result = Conditional::if($value === $firstResponse)
            ->then($firstResponse)
            ->else($secondResponse)
            ->value();

        $this->assertEquals($value, $result);
    }

    public function testGetFunctionValue()
    {
        $result = Conditional::if(fn() => true)
            ->then(fn() => fn() => 'fn 1')
            ->else(fn() => fn() => 'fn 2')
            ->value();

        $this->assertInstanceOf(Closure::class, $result);
    }
}

if (!function_exists('dump')) {
    function dump(...$expression)
    {
        var_dump($expression);
    }
}