<p align="center">
  <img src="https://static.igorora-project.net/Image/Hoa.svg" alt="Hoa" width="250px" />
</p>

---

<p align="center">
  <a href="https://travis-ci.org/igororaproject/Exception"><img src="https://img.shields.io/travis/igororaproject/Exception/master.svg" alt="Build status" /></a>
  <a href="https://coveralls.io/github/igororaproject/Exception?branch=master"><img src="https://img.shields.io/coveralls/igororaproject/Exception/master.svg" alt="Code coverage" /></a>
  <a href="https://packagist.org/packages/igorora/exception"><img src="https://img.shields.io/packagist/dt/igorora/exception.svg" alt="Packagist" /></a>
  <a href="https://igorora-project.net/LICENSE"><img src="https://img.shields.io/packagist/l/igorora/exception.svg" alt="License" /></a>
</p>
<p align="center">
  Hoa is a <strong>modular</strong>, <strong>extensible</strong> and
  <strong>structured</strong> set of PHP libraries.<br />
  Moreover, Hoa aims at being a bridge between industrial and research worlds.
</p>

# igorora\Exception

[![Help on IRC](https://img.shields.io/badge/help-%23igororaproject-ff0066.svg)](https://webchat.freenode.net/?channels=#igororaproject)
[![Help on Gitter](https://img.shields.io/badge/help-gitter-ff0066.svg)](https://gitter.im/igororaproject/central)
[![Documentation](https://img.shields.io/badge/documentation-hack_book-ff0066.svg)](https://central.igorora-project.net/Documentation/Library/Exception)
[![Board](https://img.shields.io/badge/organisation-board-ff0066.svg)](https://waffle.io/igororaproject/exception)

This library allows to use advanced exceptions. It provides generic exceptions
(that are sent over the `igorora://Event/Exception` event channel), idle exceptions
(that are not sent over an event channel), uncaught exception handlers, errors
to exceptions handler and group of exceptions (with transactions).

[Learn more](https://central.igorora-project.net/Documentation/Library/Exception).

## Installation

With [Composer](https://getcomposer.org/), to include this library into
your dependencies, you need to
require [`igorora/exception`](https://packagist.org/packages/igorora/exception):

```sh
$ composer require igorora/exception '~2.0'
```

For more installation procedures, please read [the Source
page](https://igorora-project.net/Source.html).

## Testing

Before running the test suites, the development dependencies must be installed:

```sh
$ composer install
```

Then, to run all the test suites:

```sh
$ vendor/bin/igorora test:run
```

For more information, please read the [contributor
guide](https://igorora-project.net/Literature/Contributor/Guide.html).

## Quick usage

We propose a quick overview of how to use generic exceptions, how to listen all
thrown exceptions through events and how to use group of exceptions.

### Generic exceptions

An exception is constitued of:
  * A message,
  * A code (optional),
  * A list of arguments for the message (à la `printf`, optional),
  * A previous exception (optional).

Thus, the following example builds an exception:

```php
$exception = new igorora\Exception\Exception('Hello %s!', 0, 'world');
```

The exception message will be: `Hello world!`. The “raise” message (with all
information, not only the message) is:

```
{main}: (0) Hello world!
in … at line ….
```

Previous exceptions are shown too, for instance:

```php
$previous  = new igorora\Exception\Exception('Hello previous.');
$exception = new igorora\Exception\Exception('Hello %s!', 0, 'world', $previous);

echo $exception->raise(true);

/**
 * Will output:
 *     {main}: (0) Hello world!
 *     in … at line ….
 *     
 *         ⬇
 *     
 *     Nested exception (igorora\Exception\Exception):
 *     {main}: (0) Hello previous.
 *     in … at line ….
 */
```

### Listen exceptions through events

Most exceptions in Hoa extend `igorora\Exception\Exception`, which fire themselves
on the `igorora://Event/Exception` event channel (please, see [the `igorora\Event`
library](http://central.igorora-project.net/Resource/Library/Event)). Consequently,
we can listen for all exceptions that are thrown in the application by writing:

```php
igorora\Event\Event::getEvent('igorora://Event/Exception')->attach(
    function (igorora\Event\Bucket $bucket) {
        $exception = $bucket->getData();
        // …
    }
);
```

Only the `igorora\Exception\Idle` exceptions are not fired on the channel event.

### Group and transactions

Groups of exceptions are represented by the `igorora\Exception\Group`. A group is an
exception that contains one or many exceptions. A transactional API is provided
to add more exceptions in the group with the following methods:
  * `beginTransaction` to start a transaction,
  * `rollbackTransaction` to remove all newly added exceptions since
    `beginTransaction` call,
  * `commitTransaction` to merge all newly added exceptions in the previous
    transaction,
  * `hasUncommittedExceptions` to check whether they are pending exceptions or
    not.

For instance, if an exceptional behavior is due to several reasons, a group of
exceptions can be thrown instead of one exception. Group can be nested too,
which is useful to represent a tree of exceptions. Thus:

```php
// A group of exceptions.
$group           = new igorora\Exception\Group('Failed because of several reasons.');
$group['first']  = new igorora\Exception\Exception('First reason');
$group['second'] = new igorora\Exception\Exception('Second reason');

// Can nest another group.
$group['third']           = new igorora\Exception\Group('Third reason');
$group['third']['fourth'] = new igorora\Exception\Exception('Fourth reason');

echo $group->raise(true);

/**
 * Will output:
 *     {main}: (0) Failed because of several reasons.
 *     in … at line ….
 *     
 *     Contains the following exceptions:
 *     
 *       • {main}: (0) First reason
 *         in … at line ….
 *     
 *       • {main}: (0) Second reason
 *         in … at line ….
 *     
 *       • {main}: (0) Third reason
 *         in … at line ….
 *         
 *         Contains the following exceptions:
 *         
 *           • {main}: (0) Fourth reason
 *             in … at line ….
 */
```

The following example uses a transaction to add new exceptions in the group:

```php
$group   = new igorora\Exception\Group('Failed because of several reasons.');
$group[] = new igorora\Exception\Exception('Always present.');

$group->beginTransaction();

$group[] = new igorora\Exception\Exception('Might be present.');

if (true === $condition) {
    $group->commitTransaction();
} else {
    $group->rollbackTransaction();
}
```

## Documentation

The
[hack book of `igorora\Exception`](https://central.igorora-project.net/Documentation/Library/Exception)
contains detailed information about how to use this library and how it works.

To generate the documentation locally, execute the following commands:

```sh
$ composer require --dev igorora/devtools
$ vendor/bin/igorora devtools:documentation --open
```

More documentation can be found on the project's website:
[igorora-project.net](https://igorora-project.net/).

## Getting help

There are mainly two ways to get help:

  * On the [`#igororaproject`](https://webchat.freenode.net/?channels=#igororaproject)
    IRC channel,
  * On the forum at [users.igorora-project.net](https://users.igorora-project.net).

## Contribution

Do you want to contribute? Thanks! A detailed [contributor
guide](https://igorora-project.net/Literature/Contributor/Guide.html) explains
everything you need to know.

## License

Hoa is under the New BSD License (BSD-3-Clause). Please, see
[`LICENSE`](https://igorora-project.net/LICENSE) for details.
