<?php

namespace Conditional\Tests;

use Closure;
use Conditional\Conditional;
use PHPUnit\Framework\TestCase;
use Conditional\Exceptions\InvalidConditionOrderException;

class ConditionalTest extends TestCase
{
    public function testConditionalHelper()
    {
        $this->assertEquals(Conditional::if(true), conditional(true));
    }

    public function testInstanceOfConditionalNeedsIfStatementBeforeOtherStatement()
    {

        $conditional = new Conditional();

        $this->expectException(InvalidConditionOrderException::class);

        $this->expectExceptionMessage('you need to make at least one condition before calling then()');

        $conditional->then(1);
        $conditional->else(2);
    }

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

        $value = rand(1, 2);

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

    public function testInvocableClassValue()
    {
        $invocable = new class {

            public function __invoke($value = '')
            {
                return $value ?: 'Invocable';
            }
        };

        $result = Conditional::if($invocable() === 'Invocable')
            ->then($invocable)
            ->else($invocable(1))
            ->value();

        $this->assertEquals(true, is_string($result));
    }

    public function testElseIfCannotBeCalledBeforeThen()
    {
        $condtional = new Conditional();

        $this->expectException(InvalidConditionOrderException::class);

        $this->expectExceptionMessage('you need to call then() condition before calling elseIf');

        $condtional->elseIf(true);
    }
}

if (!function_exists('dump')) {
    function dump(...$expression)
    {
        var_dump($expression);
    }
}