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
        $conditional1 = Conditional::if((rand(2, 3) % 2) === 1);

        $conditional = new Conditional();

        $this->expectException(InvalidConditionOrderException::class);

        $this->expectExceptionMessage('you need to make at least one condition before calling then()');

        $conditional1->then(fn() => $conditional->then(1));
        $conditional1->then(fn() => $conditional->else(2));
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

    public function testInvokableClassValue()
    {
        $invokable = new class {

            public function __invoke($value = '')
            {
                return $value ?: 'Invocable';
            }
        };

        $result1 = Conditional::if($invokable() === 'Invocable')
            ->then($invokable)
            ->else($invokable(1))
            ->value();

        $result2 = Conditional::if(strlen('abcd') === 4)
            ->then(new Invokable())
            ->value();

        $this->assertEquals(true, is_string($result1));
        $this->assertEquals(true, is_int($result2));
    }

    private function dump(...$expression)
    {
        var_dump($expression);
    }

    private function dd()
    {
        die($this->dump(...func_get_args()));
    }
}

class Invokable {

    public function __invoke($value = '')
    {
        return $value ?: 1;
    }

}