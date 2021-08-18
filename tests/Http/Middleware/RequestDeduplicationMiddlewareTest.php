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

namespace Sassnowski\Roach\Tests\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Sassnowski\Roach\Http\Middleware\DropRequestException;
use Sassnowski\Roach\Http\Middleware\RequestDeduplicationMiddleware;
use Sassnowski\Roach\Testing\FakeHandler;
use Sassnowski\Roach\Testing\FakeLogger;
use Sassnowski\Roach\Tests\InteractsWithRequests;

/**
 * @group http
 * @group middleware
 *
 * @internal
 */
final class RequestDeduplicationMiddlewareTest extends TestCase
{
    use InteractsWithRequests;

    private RequestDeduplicationMiddleware $middleware;

    private FakeLogger $logger;

    private FakeHandler $handler;

    protected function setUp(): void
    {
        $this->logger = new FakeLogger();
        $this->middleware = new RequestDeduplicationMiddleware($this->logger);
        $this->handler = new FakeHandler();
    }

    public function testDropsRequestIfItWasAlreadySeenBefore(): void
    {
        $request = $this->createRequest();

        $this->middleware->handle($request, $this->handler);

        $this->expectException(DropRequestException::class);
        $this->middleware->handle($request, $this->handler);
    }

    public function testPassesRequestAlongIfItHasntBeenSeenBefore(): void
    {
        $requestA = $this->createRequest('::url-a::');
        $requestB = $this->createRequest('::url-b::');

        $this->middleware->handle($requestA, $this->handler);
        $this->middleware->handle($requestB, $this->handler);

        $this->handler->assertWasCalledWith($requestA);
        $this->handler->assertWasCalledWith($requestB);
    }

    public function testLogDroppedRequestsIfLoggerWasProvided(): void
    {
        $request = $this->createRequest();

        $this->middleware->handle($request, $this->handler);

        try {
            $this->middleware->handle($request, $this->handler);
        } catch (DropRequestException) {
        }

        self::assertTrue(
            $this->logger->messageWasLogged(
                'info',
                '[RequestDeduplicationMiddleware] Dropping duplicate request',
                ['uri' => '::url::'],
            ),
        );
    }
}
