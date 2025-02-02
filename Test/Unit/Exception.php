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

use igorora\Event;
use igorora\Exception\Exception as SUT;
use igorora\Test;

/**
 * Class \igorora\Exception\Test\Unit\Exception.
 *
 * Test suite of the exception class.
 *
 * @license    New BSD License
 */
class Exception extends Test\Unit\Suite
{
    public function case_is_an_idle_exception(): void
    {
        $this
            ->when($result = new SUT('foo'))
            ->then
                ->object($result)
                    ->isInstanceOf('igorora\Exception\Idle');
    }

    public function case_event_is_registered(): void
    {
        $this
            ->given(new SUT('foo'))
            ->when($result = Event::eventExists('igorora://Event/Exception'))
            ->then
                ->boolean($result)
                    ->isTrue();
    }

    public function case_event_is_sent(): void
    {
        $self = $this;

        $this
            ->given(
                Event::getEvent('igorora://Event/Exception')->attach(
                    function (Event\Bucket $bucket) use ($self, &$called): void {
                        $called = true;

                        $self
                            ->object($bucket->getSource())
                                ->isInstanceOf('igorora\Exception\Exception')
                            ->string($bucket->getSource()->getMessage())
                                ->isEqualTo('foo')
                            ->object($bucket->getData())
                                ->isIdenticalTo($bucket->getSource());
                    }
                )
            )
            ->when(new SUT('foo'))
            ->then
                ->boolean($called)
                    ->isTrue();
    }
}
