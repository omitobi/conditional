<?php

namespace Conditional;

use Closure;
use Conditional\Exceptions\InvalidConditionOrderException;

/**
 * Class Conditional
 * @package Conditional
 *
 */
class Conditional
{
    private static bool $truthy = false;

    private static bool $conditionsExists = false;

    private static bool $ifCalled = false;

    private static bool $thenCalled = false;

    private static bool $elseCalled = false;

    private static bool $elseIfCalled = false;

    private static $finalValue;

    private static $finalValueChanged = null;

    public static function if($condition)
    {
        self::setTruthy($condition);

        self::$conditionsExists = true;

        self::$ifCalled = true;

        return new static;
    }

    public static function setTruthy($condition)
    {
        if (!$condition instanceof Closure) {
            self::$truthy = (bool)$condition;
        } else {
            self::$truthy = (bool)$condition();
        }
    }

    public function else($action)
    {
        if (!self::$thenCalled) {
            throw new InvalidConditionOrderException(
                'then() must be called before calling else()'
            );
        }

        $this->toggleTruthy();

        self::$conditionsExists = true;

        self::$elseCalled = true;

        return $this->then($action);
    }

    public function then($action)
    {
        if (!$this->allowThen()) {
            throw new InvalidConditionOrderException(
                'A condition must be called before calling then()'
            );
        }

        if (self::$truthy) {

            if ($this->isExceptionClass($action)) {
                throw $action;
            }

            if (!$this->canBeCalled($action)) {
                $action = fn() => $action;
            }

            self::$finalValue = $action();

            self::$finalValueChanged = true;
        }

        self::$thenCalled = true;

        self::$conditionsExists = false;

        return $this;
    }


    public function elseIf($condition)
    {
        if (! self::$thenCalled || self::$elseCalled || self::$elseIfCalled) {
            throw new InvalidConditionOrderException(
                'At least then() condition must be called before calling elseIf'
            );
        }

        self::$conditionsExists = true;

        if (self::$truthy) {
            $this->toggleTruthy();

            return $this;
        }

        self::setTruthy($condition);

        self::$elseIfCalled = true;

        return $this;
    }

    public function value()
    {
        return self::$finalValue;
    }

    private function allowThen()
    {
        return self::$conditionsExists && (self::$ifCalled || self::$elseIfCalled);
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

    private function isExceptionClass($action)
    {
        return is_a($action, \Exception::class);
    }

    public function __destruct()
    {
        self::$truthy = false;
        self::$conditionsExists = false;
        self::$ifCalled = false;
        self::$thenCalled = false;
        self::$elseCalled = false;
        self::$elseIfCalled = false;
        self::$finalValue = null;
        self::$finalValueChanged = null;
    }
}
