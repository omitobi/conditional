<?php

namespace Conditional;

use Closure;
use Conditional\Conditional\Exceptions\InvalidConditionOrderException;

class Conditional
{
    private static bool $truthy = false;

    private static bool $conditionsExists = false;

    private static bool $ifExists = false;

    private static bool $thenCalled = false;

    private static $finalValue;

    public static function if($condition)
    {
        static::$conditionsExists = true;

        static::$ifExists = true;

        if (!$condition instanceof Closure) {
            static::$truthy = (bool)$condition;
        } else {
            static::$truthy = (bool)$condition();
        }

        return new static;
    }

    public function elseIf()
    {
        //todo.addition
    }

    public function else($action)
    {
        static::$conditionsExists = true;

        $this->toggleTruthy();

        if (!static::$thenCalled) {
            throw new InvalidConditionOrderException(
                'you need to call then() condition before calling else()'
            );
        }

        return $this->then($action);
    }

    public function then($action)
    {
        if (!static::$conditionsExists || !static::$ifExists) {
            throw new InvalidConditionOrderException(
                'you need to make another condition before calling then()'
            );
        }

        if (!$action instanceof Closure) {
            $action = fn() => $action;
        }

        if (static::$truthy) {
            static::$finalValue = $action();
        }

        static::$thenCalled = true;

        static::$conditionsExists = false;

        return $this;
    }

    private function toggleTruthy()
    {
        static::$truthy = !static::$truthy;
    }

    public function value()
    {
        return static::$finalValue;
    }
}
