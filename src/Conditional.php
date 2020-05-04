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
    private $truthy = false;

    private $conditionsExists = false;

    private $ifCalled = false;

    private $thenCalled = false;

    private $elseCalled = false;

    private $elseIfCalled = false;

    private $finalValue;

    private $finalValueChanged = null;

    public static function if($condition)
    {
        return (new static)->on($condition);
    }

    public function on($condition)
    {
        $this->setTruthy($condition);

        $this->conditionsExists = true;

        $this->ifCalled = true;

        return $this;
    }

    public function setTruthy($condition)
    {
        if (!$condition instanceof Closure) {
            $this->truthy = (bool)$condition;
        } else {
            $this->truthy = (bool)$condition();
        }
    }

    public function else($action)
    {
        if (!$this->thenCalled) {
            throw new InvalidConditionOrderException(
                'then() must be called before calling else()'
            );
        }

        $this->toggleTruthy();

        $this->conditionsExists = true;

        $this->elseCalled = true;

        return $this->then($action)->value();
    }

    public function then($action)
    {
        if (!$this->allowThen()) {
            throw new InvalidConditionOrderException(
                'A condition must be called before calling then()'
            );
        }

        if ($this->truthy) {

            if ($this->isExceptionClass($action)) {
                throw $action;
            }

            if (!$this->canBeCalled($action)) {
                $action = fn() => $action;
            }

            $this->finalValue = $action();

            $this->finalValueChanged = true;
        }

        $this->thenCalled = true;

        $this->conditionsExists = false;

        return $this;
    }


    public function elseIf($condition)
    {
        if (!$this->allowElseIf()) {
            throw new InvalidConditionOrderException(
                'At least then() condition must be called before calling elseIf'
            );
        }

        $this->conditionsExists = true;

        if ($this->truthy) {
            $this->toggleTruthy();

            return $this;
        }

        $this->setTruthy($condition);

        $this->elseIfCalled = true;

        return $this;
    }

    private function isExceptionClass($action)
    {
        return is_a($action, \Exception::class);
    }

    public function value()
    {
        return $this->finalValue;
    }

    private function allowElseIf()
    {
        return $this->thenCalled &&
            !$this->conditionsExists &&
            !$this->elseCalled;
    }

    private function allowThen()
    {
        return $this->conditionsExists && ($this->ifCalled || $this->elseIfCalled);
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
        $this->truthy = !$this->truthy;
    }
}
