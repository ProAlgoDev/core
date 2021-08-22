<?php

declare(strict_types=1);

/**
 * Copyright (c) 2021 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/roach-php/roach
 */

namespace Sassnowski\Roach\Tests\Downloader\Middleware;

use PHPUnit\Framework\TestCase;
use Sassnowski\Roach\Downloader\Middleware\LoggerMiddleware;
use Sassnowski\Roach\Testing\FakeLogger;
use Sassnowski\Roach\Tests\InteractsWithRequests;

/**
 * @internal
 * @group downloader
 */
final class LoggerMiddlewareTest extends TestCase
{
    use InteractsWithRequests;

    private FakeLogger $logger;

    private LoggerMiddleware $middleware;

    protected function setUp(): void
    {
        $this->logger = new FakeLogger();
        $this->middleware = new LoggerMiddleware($this->logger);
    }

    public function testLogScheduledRequest(): void
    {
        $request = $this->createRequest('::uri::');

        $this->middleware->handleRequest($request);

        self::assertTrue(
            $this->logger->messageWasLogged('info', '[LoggerMiddleware] Dispatching request', [
                'uri' => '::uri::',
            ]),
        );
    }
}
