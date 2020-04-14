<?php

namespace Conditional;

use Closure;
use Conditional\Exceptions\InvalidConditionOrderException;

class Conditional
{
    private static bool $truthy = false;

    private static bool $conditionsExists = false;

    private static bool $ifCalled = false;

    private static bool $thenCalled = false;

    private static $finalValue;

    public static function if($condition)
    {
        self::$conditionsExists = true;

        self::$ifCalled = true;

        if (!$condition instanceof Closure) {
            self::$truthy = (bool)$condition;
        } else {
            self::$truthy = (bool)$condition();
        }

        return new static;
    }

    public function else($action)
    {
        self::$conditionsExists = true;

        $this->toggleTruthy();

        if (!self::$thenCalled) {
            throw new InvalidConditionOrderException(
                'then() must be called before calling else()'
            );
        }

        return $this->then($action);
    }

    public function then($action)
    {
        if (!self::$conditionsExists || !self::$ifCalled) {
            throw new InvalidConditionOrderException(
                'A condition must be called before calling then()'
            );
        }

        if (!$this->canBeCalled($action)) {
            $action = fn() => $action;
        }

        if (self::$truthy) {
            self::$finalValue = $action();
        }

        self::$thenCalled = true;

        self::$conditionsExists = false;

        return $this;
    }

    public function value()
    {
        return self::$finalValue;
    }

    protected function canBeCalled($value)
    {
        return (
            (is_object($value) && method_exists($value, '__invoke')) ||
            ($value instanceof Closure)
        );
    }

    private function toggleTruthy()
    {
        self::$truthy = !self::$truthy;
    }

    public function __destruct()
    {
        self::$truthy = false;
        self::$conditionsExists = false;
        self::$ifCalled = false;
        self::$thenCalled = false;
        self::$finalValue = null;
    }
}
