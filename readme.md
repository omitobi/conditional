<p align="center">
<img src="https://github.com/omitobi/assets/blob/master/conditional/twitter_header_photo_2.png">
</p>

<p align="center">
<a href="https://travis-ci.com/omitobi/conditional"> <img src="https://travis-ci.com/omitobi/conditional.svg?branch=master" alt="Build Status"/></a>
<a href="https://packagist.org/packages/omitobisam/conditional"> <img src="https://poser.pugx.org/omitobisam/conditional/v/stable" alt="Latest Stable Version"/></a>
<a href="https://packagist.org/packages/omitobisam/conditional"> <img src="https://poser.pugx.org/omitobisam/conditional/downloads" alt="Total Downloads"/></a>
<a href="https://packagist.org/packages/omitobisam/conditional"> <img src="https://poser.pugx.org/omitobisam/conditional/v/unstable" alt="Latest Unstable Version"/></a>
<a href="https://packagist.org/packages/omitobisam/conditional"> <img src="https://poser.pugx.org/omitobisam/conditional/d/monthly" alt="Latest Monthly Downloads"/></a>
  <a href="https://packagist.org/packages/omitobisam/conditional"> <img src="https://poser.pugx.org/omitobisam/conditional/license" alt="License"/></a>
</p>

## About Conditional

A (optional but) fluent helper for object-oriented style of if-else statements.

It helps you construct conditionals as you speak it object Oriented way.

> Some use cases are not yet covered so you can default to `if() else {}` statement.

## Minimum Requirement

- PHP 7.4
- (Older version compatibility to arrive with upcoming version of this package)

## Installation

Using composer:

`composer require omitobisam/conditional`

or add to the require object of `composer.json` file with the version number:

```json
{
  "require": {
    "omitobisam/conditional": "^1.1" 
  }
}
```

After this run `composer update`

## Usage

You can call it simply statically:

```php

use Conditional\Conditional;
$data = null;

Conditional::if(is_null($data))
    ->then(fn() => doThis())
    ->else(fn() => doThat());

```

Conditional also comes with a helper function called `conditional()` and its used like so:

```php
conditional(isset($data))
    ->then(fn() => doThis())
    ->else(fn() => doThat());
```

:tada: Now like a tenary operator. Conditional at version 1.2 `else()` immediately returns the value of the last truthy execution:

```php
conditional('1' === 'a', 1, 2); //returns 2 - without calling ->value()

conditional(false, 1)

  ->else(2); //returns 2 - without calling ->value()

// Of course the normal one
conditional(false)

  ->then(1)

  ->else(2); //returns 2
```

You can also evaluate a closure call on the conditional `if` method:

```php
use Conditional\Conditional;

Conditional::if(fn() =>  '1' == 1) // or conditional(fn() => 1 + 1)
    ->then(fn() => doThis()) // doThis() is executed
    ->else(fn() => doThat());
```

Conditional also allows returning values passed in the conditions.
You use `value()` method to get the values either the result of the closure passed or the values as they are. 

```php
use Conditional\Conditional;

$value = Conditional::if(fn() => 'a' !== 1) // or conditional(fn() => 'a' !== 1)
    ->then(1)
    ->value(); // returns 2 (because a !== 1)

//do something with $value
```

Finally, `then()`  and `else()` methods accepts invokable class or objects. Lets see:

```php
use Conditional\Conditional;

class Invokable {

    public function __invoke()
    {
        return 'I was Invoked';
    }
}

$invokableClass = new Invokable();

$value = Conditional::if(fn() => 'a' === 1) // or conditional(fn() => 1 + 1)
    ->then(1)
    ->else($invokableClass); //Value returns 'I was Invoked'

// Do something with $value
```

You are also allowed to throw exception based on the condition like so:

```php
 \conditional('foo' === 'bar')

    ->then('foo === bar')

    ->else(new TestException('Foo is not the same as bar'));  //this exception is thrown
```

### Newly released

`elseIf()` method of Conditional like so:

```php
conditional(isset($data))

    ->then(fn() => doThis())

    ->elseIf(is_int(1))

    ->then(fn() => doThat())

    ->else(2);
```

`elseIf()` can be called multiple times on an instance:

```php
$value = Conditional::if(false)

    ->then('a')

    ->elseIf('b' == 1) //first one

    ->then('b')

    ->elseIf('b' !== 2) //another

    ->then('2')

    ->else(1);

// $value is '2'
```

### Coming Soon

`If()` and `elseIf()` statement accepting a default value when no condition is met and `else()` is not called like so:

```php
Conditional::if(is_array('a'), 'ninja') //default value is ninja

    ->then(fn() => doThis())

    ->elseIf(is_int(""))

    ->then(fn() => doThat())
    
    ->value(); // 'ninja' is returned :scream:
```

Multiple conditional check like `a && b && c && d` or `a || b || c ||...` syntax

- Help wanted for this

## Caveats (or Awareness)

- As at version 1.x, Calling `if()` method returns an instance of Condtional, so do not call it twice on the same instance for example:

```php
// This is Wrong!

Conditional::if(true)
    ->then(1)
    ->else(2)
    ->if('1'); // Don't do it except you intend to start a new and fresh if Conditional
```
> See: tests/ConditionalTest::testEveryIfCallCreatesNewFreshInstance test. On the last line of that test, the two conditionals are not the same.
- Conditional uses `if..else` statements in implementation, how cool is that? :smile:
- Conditional relies on closures to return non closure values passed to then.
> In the future release it will be optional for `then` and `else` method
- This project at the initial phase is a proof of concept so performance and best practices (?)
> It might be part of something great in the future (especially as a Laravel helper) how cool that would be!

## Contributions

- More tests are needed
- Issues have been opened
- Needs to review PR about adding `elseIf()` method
- How about those static properties, any idea how to reduce the number of static properties used?
- Performance optimization (?)

## Development

For new feature, checkout with prefix `feature/#issueid` e.g `feature/#100-add-auto-deloy`

- 
- Clone this repository
- run `sh dockerizer.sh` or `bash dockerizer.sh`
- execute into the docker environment with `docker-compose exec conditional sh` (`sh` can be another bash)
- run tests with `vendor/bin/phpunit`

## Licence

MIT (see LICENCE file)
