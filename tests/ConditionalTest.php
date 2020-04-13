<?php

namespace Conditional\Tests;

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
}

if (!function_exists('dump')) {
    function dump(...$expression)
    {
        var_dump($expression);
    }
}