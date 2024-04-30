<?php

declare(strict_types=1);

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
    private bool $truthy = false;

    private bool $conditionsExists = false;

    private bool $ifCalled = false;

    private bool $thenCalled = false;

    private bool $elseCalled = false;

    private bool $elseIfCalled = false;

    private $finalValue;

    private ?bool $finalValueChanged = null;

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

    public function then($action): self
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
                $action = function () use ($action) {
                    return $action;
                };
            }

            $this->finalValue = $action();

            $this->finalValueChanged = true;
        }

        $this->thenCalled = true;

        $this->conditionsExists = false;

        return $this;
    }


    public function elseIf($condition): self
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

    private function isExceptionClass($action): bool
    {
        return is_a($action, \Throwable::class);
    }

    public function value()
    {
        return $this->finalValue;
    }

    private function allowElseIf(): bool
    {
        return $this->thenCalled &&
            !$this->conditionsExists &&
            !$this->elseCalled;
    }

    private function allowThen(): bool
    {
        return $this->conditionsExists && ($this->ifCalled || $this->elseIfCalled);
    }

    protected function canBeCalled($value): bool
    {
        return (
            (is_object($value) && method_exists($value, '__invoke')) ||
            ($value instanceof Closure)
        );
    }

    private function toggleTruthy(): void
    {
        $this->truthy = !$this->truthy;
    }
}
