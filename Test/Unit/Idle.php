<?php

declare(strict_types=1);

/**
 * Hoa
 *
 *
 * @license
 *
 * New BSD License
 *
 * Copyright © 2007-2017, Hoa community. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Hoa nor the names of its contributors may be
 *       used to endorse or promote products derived from this software without
 *       specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS AND CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace igorora\Exception\Test\Unit;

use igorora\Exception\Idle as SUT;
use igorora\Test;

/**
 * Class \igorora\Exception\Test\Unit\Idle.
 *
 * Test suite of the idle exception.
 *
 * @license    New BSD License
 */
class Idle extends Test\Unit\Suite
{
    public function case_is_a_real_exception(): void
    {
        $this
            ->when($result = new SUT('foo'))
            ->then
                ->object($result)
                    ->isInstanceOf('Exception');
    }

    public function case_get_backtrace(): void
    {
        $this
            ->given($exception = new SUT('foo'))
            ->when($result = $exception->getBacktrace())
            ->then
                ->array($result)
                    ->hasKey(0)
                ->array($result[0])
                    ->hasKey('file')
                    ->hasKey('line')
                    ->hasKey('function')
                    ->hasKey('class')
                    ->hasKey('type')
                    ->hasKey('args');
    }

    public function case_get_previous_throw(): void
    {
        $this
            ->given(
                $previous  = new SUT('previous'),
                $exception = new SUT('foo', 0, [], $previous)
            )
            ->when($result = $exception->getPreviousThrow())
            ->then
                ->object($result)
                    ->isIdenticalTo($previous);
    }

    public function case_get_arguments(): void
    {
        $this
            ->given($exception = new SUT('foo', 0, ['arg', 42, null]))
            ->when($result = $exception->getArguments())
            ->then
                ->array($result)
                    ->isEqualTo(['arg', 42, '(null)']);
    }

    public function case_get_arguments_from_a_string(): void
    {
        $this
            ->given($exception = new SUT('foo', 0, 'arg'))
            ->when($result = $exception->getArguments())
            ->then
                ->array($result)
                    ->isEqualTo(['arg']);
    }

    public function case_get_raw_message(): void
    {
        $this
            ->given(
                $message   = 'foo %s',
                $exception = new SUT($message)
            )
            ->when($result = $exception->getRawMessage())
            ->then
                ->string($result)
                    ->isEqualTo($message);
    }

    public function case_get_formatted_message(): void
    {
        $this
            ->given(
                $message   = 'foo %s',
                $exception = new SUT($message, 0, 'bar')
            )
            ->when($result = $exception->getFormattedMessage())
            ->then
                ->string($result)
                    ->isEqualTo($exception->getMessage())
                    ->isEqualTo('foo bar');
    }

    public function case_get_from_object(): void
    {
        $this
            ->given($exception = new SUT('foo'))
            ->when($result = $exception->getFrom())
            ->then
                ->string($result)
                    ->isEqualTo(__METHOD__ . '()');
    }

    public function case_raise(): void
    {
        $this
            ->given($exception = new SUT('foo'), $line = __LINE__)
            ->when($result = $exception->raise())
            ->then
                ->string($result)
                    ->isEqualTo(
                        __METHOD__ . '(): (0) foo' . "\n" .
                        'in ' . __FILE__ . ' at line ' . $line . '.'
                    );
    }

    public function case_raise_with_previous(): void
    {
        $this
            ->given(
                $previous     = new SUT('previous'),
                $previousLine = __LINE__ - 1,
                $exception    = new SUT('foo', 0, [], $previous),
                $line         = __LINE__ - 1
            )
            ->when($result = $exception->raise(true))
            ->then
                ->string($result)
                    ->isEqualTo(
                        __METHOD__ . '(): (0) foo' . "\n" .
                        'in ' . __FILE__ . ' at line ' . $line . '.' . "\n\n" .
                        '    ⬇' . "\n\n" .
                        'Nested exception (' . get_class($previous) . '):' . "\n" .
                        __METHOD__ . '(): (0) previous' . "\n" .
                        'in ' . __FILE__ . ' at line ' . $previousLine . '.'
                    );
    }

    public function case_uncaught(): void
    {
        $this
            ->given(
                $this->function->ob_get_level = 0,
                $exception                    = new SUT('foo'),
                $line                         = __LINE__ - 1
            )
            ->when($result = SUT::uncaught($exception))
            ->then
                ->variable($result)
                    ->isNull()
                ->output
                    ->isEqualTo(
                        'Uncaught exception (' . get_class($exception) . '):' . "\n" .
                        __METHOD__ . '(): (0) foo' . "\n" .
                        'in ' . __FILE__ . ' at line ' . $line . '.'
                    );
    }

    public function case_uncaught_not_Hoa(): void
    {
        $this
            ->exception(function (): void {
                SUT::uncaught(new \Exception('foo'));
            })
                ->isInstanceOf('Exception')
            ->output
                ->isEmpty();
    }

    public function case_to_string(): void
    {
        $this
            ->given($exception = new SUT('foo'))
            ->when($result = $exception->__toString())
            ->then
                ->string($result)
                    ->isEqualTo($exception->raise());
    }

    public function case_disable_uncaught_handler(): void
    {
        $this
            ->given(
                $this->function->restore_exception_handler = function () use (&$called) {
                    $called = true;

                    return null;
                }
            )
            ->when($result = SUT::enableUncaughtHandler(false))
            ->then
                ->variable($result)
                    ->isNull()
                ->boolean($called)
                    ->isTrue();
    }

    public function case_enable_uncaught_handler(): void
    {
        $self = $this;

        $this
            ->given(
                $this->function->set_exception_handler = function ($handler) use ($self, &$called) {
                    $called = true;

                    $self
                        ->object($handler)
                            ->isInstanceOf('Closure')
                        ->let($reflection = new \ReflectionObject($handler))
                        ->array($invokeParameters = $reflection->getMethod('__invoke')->getParameters())
                            ->hasSize(1)
                        ->string($invokeParameters[0]->getName())
                            ->isEqualTo('exception');

                    return null;
                }
            )
            ->when($result = SUT::enableUncaughtHandler())
            ->then
                ->variable($result)
                    ->isNull()
                ->boolean($called)
                    ->isTrue();
    }
}
