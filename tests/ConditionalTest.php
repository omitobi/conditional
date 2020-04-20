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

    public function testThenCannotBeCalledBeforeIf()
    {
        $conditional = new Conditional();

        $this->expectException(InvalidConditionOrderException::class);

        $this->expectExceptionMessage('A condition must be called before calling then()');

        $conditional->then(1);
    }

    public function testElseCannotBeCalledBeforeThen()
    {
        $conditional = new Conditional();

        $this->expectException(InvalidConditionOrderException::class);

        $this->expectExceptionMessage('then() must be called before calling else()');

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

    public function testEveryIfCallCreatesNewFreshInstance()
    {
        $conditional = new Conditional();

        $instanceOne = $conditional->if(false);
        $instanceTwo = $conditional->if(false);

        $this->assertInstanceOf(Conditional::class, $instanceOne);
        $this->assertInstanceOf(Conditional::class, $instanceTwo);

        $this->assertNotSame($instanceOne, $instanceTwo);
    }

    public function testThenAndElseAcceptsException()
    {
        $this->expectException(TestException::class);
        $this->expectExceptionMessage('This is still wrong');

        \conditional('foo' === 'bar')
            ->then(new TestException('This is wrong'))
            ->else(new TestException('This is still wrong'));
    }

    public function testElseAfterElseIfConditional()
    {
        $value = Conditional::if('1' === true)
            ->then(1)
            ->elseIf('1' === false)
            ->then(3)
            ->else(4)
            ->value();

        $this->assertEquals(4, $value);
    }

    public function testElseIfCannotBeCalledBeforeThen()
    {
        $condtional = new Conditional();

        $this->expectException(InvalidConditionOrderException::class);

        $this->expectExceptionMessage('At least then() condition must be called before calling elseIf');

        $condtional->elseIf(true);
    }

    public function testElseIfCannotBeCalledAfterElse()
    {
        $this->expectException(InvalidConditionOrderException::class);

        $this->expectExceptionMessage('At least then() condition must be called before calling elseIf');

        Conditional::if('1' === true)
            ->then(1)
            ->elseIf('1' === false)
            ->then(3)
            ->else(5)
            ->elseIf(true)
            ->then('abc');
    }

    public function testElseIfBuildsUpAnotherConditional()
    {
        $conditional = Conditional::if(false);

        $conditional2 = $conditional->then(1)
            ->elseIf(true);

        $this->assertEquals($conditional, $conditional2);
        $this->assertSame($conditional, $conditional2);

        $value = $conditional2->then(3)
            ->value();

        $this->assertEquals(3, $value);
    }

    public function testElseIfCannotBeChainedWithElseIf()
    {
        $this->expectException(InvalidConditionOrderException::class);

        $this->expectExceptionMessage('At least then() condition must be called before calling elseIf');

        Conditional::if('1' === true)
            ->then(1)
            ->elseIf('1' === false)
            ->elseIf(1 === '1')
            ->value();
    }

    public function testElseIfCanBeCalledMultipleTimesInAnInstance()
    {
        $value = Conditional::if(false)

            ->then('a')

            ->elseIf('b' == 1)

            ->then('b')

            ->elseIf('b' !== 2)

            ->then('2')

            ->else(1)

            ->value();

        $this->assertEquals($value, 2);
    }

    public function testConditionalHelperFunctionAcceptsThenAndElseValues()
    {
        $this->assertSame('2', conditional(1 == '1', '2')->value());
        $this->assertSame('3', conditional(1 === '1', '2', '3')->value());

        $this->assertSame(
            conditional(1 === 2, '2')->elseIf(is_string('a'))
                ->then('a')->value(),
            'a'
        );
    }

//    public function testIfCannotBeCalledAfterElseIf()
//    {
//        $this->expectException(InvalidConditionOrderException::class);
//
//        $this->expectExceptionMessage('At least then() condition must be called before calling elseIf');
//
//        Conditional::if('1' === true)
//            ->then(1)
//            ->elseIf('1' === false)
//            ->if(1 === '1')
//            ->value();
//    }

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

class TestException extends \Exception {

}