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

namespace Sassnowski\Roach\Tests\Parsing;

use Closure;
use PHPUnit\Framework\TestCase;
use Sassnowski\Roach\Http\Response;
use Sassnowski\Roach\Parsing\Handlers\FakeHandler;
use Sassnowski\Roach\Parsing\MiddlewareStack;
use Sassnowski\Roach\Parsing\ParseResult;
use Sassnowski\Roach\Tests\InteractsWithRequests;
use Sassnowski\Roach\Tests\InteractsWithResponses;

/**
 * @internal
 */
final class MiddlewareStackTest extends TestCase
{
    use InteractsWithRequests;
    use InteractsWithResponses;

    private MiddlewareStack $middlewareStack;

    protected function setUp(): void
    {
        $this->middlewareStack = new MiddlewareStack();
    }

    public function testCallsCallbackOnRequest(): void
    {
        $parseCallback = static fn () => null;
        $expectedRequest = ParseResult::request('::new-url::', $parseCallback);
        $request = $this->createRequest(callback: static fn () => yield $expectedRequest);
        $response = $this->makeResponse($request);

        $result = \iterator_to_array($this->middlewareStack->handle($response));

        self::assertEquals([$expectedRequest], $result);
    }

    public function testCallsHandlersForIncomingResponses(): void
    {
        $handler = $this->makeHandler();
        $request = $this->createRequest(callback: static fn () => yield ParseResult::item([]));
        $response = $this->makeResponse($request);
        $stack = new MiddlewareStack([$handler]);

        $stack->handle($response)->next();

        $handler->assertResponseHandled($response);
    }

    public function testCallResponseHandlersInOrder(): void
    {
        $handlerA = $this->makeHandler(static function (Response $response) {
            return $response->withMeta('foo', $response->getMeta('foo') . 'A');
        });
        $handlerB = $this->makeHandler(static function (Response $response) {
            return $response->withMeta('foo', $response->getMeta('foo') . 'B');
        });
        $request = $this->createRequest(callback: static function (Response $response) {
            self::assertEquals('AB', $response->getMeta('foo'));

            yield ParseResult::item([]);
        });
        $stack = new MiddlewareStack([$handlerA, $handlerB]);

        $stack->handle($this->makeResponse($request))->next();
    }

    public function testPassesEachNewRequestToHandlersInOrder(): void
    {
        $handlerA = $this->makeHandler(
            handleRequestCallback: static fn ($r) => $r->withMeta('::key::', $r->getMeta('::key::', '') . 'A'),
        );
        $handlerB = $this->makeHandler(
            handleRequestCallback: static fn ($r) => $r->withMeta('::key::', $r->getMeta('::key::', '') . 'B'),
        );
        $results = [
            ParseResult::request('::url::', static fn () => null),
            ParseResult::request('::url::', static fn () => null),
        ];
        $request = $this->createRequest(callback: static fn () => yield from $results);
        $stack = MiddlewareStack::create($handlerA, $handlerB);

        $actual = \iterator_to_array($stack->handle($this->makeResponse($request)));

        self::assertSame('AB', $actual[0]->value()->getMeta('::key::'));
        self::assertSame('AB', $actual[1]->value()->getMeta('::key::'));
    }

    public function testDoesNotPassOnRequestIfDroppedByHandler(): void
    {
        $dropHandler = $this->makeHandler(handleRequestCallback: static function ($request, $response, $dropRequest) {
            return $dropRequest();
        });
        $handlerB = $this->makeHandler();
        $request = $this->createRequest(
            callback: fn () => yield ParseResult::fromValue($this->createRequest()),
        );
        $stack = new MiddlewareStack([$dropHandler, $handlerB]);

        $result = \iterator_to_array($stack->handle($this->makeResponse($request)));

        $handlerB->assertNoRequestHandled();
        self::assertEmpty($result);
    }

    public function testCallsItemHandlersInOrderForOutgoingItems(): void
    {
        $handlerA = $this->makeHandler(
            handleItemCallback: static fn ($item) => $item->set('::key::', $item->get('::key::', '') . 'A'),
        );
        $handlerB = $this->makeHandler(
            handleItemCallback: static fn ($item) => $item->set('::key::', $item->get('::key::', '') . 'B'),
        );
        $request = $this->createRequest(callback: static function (Response $response) {
            yield ParseResult::item([]);
        });

        $result = MiddlewareStack::create($handlerA, $handlerB)
            ->handle($this->makeResponse($request))
            ->current();

        self::assertSame('AB', $result->value()->get('::key::'));
    }

    private function makeHandler(
        ?Closure $handleResponse = null,
        ?Closure $handleItemCallback = null,
        ?Closure $handleRequestCallback = null,
    ): FakeHandler {
        return new FakeHandler($handleResponse, $handleItemCallback, $handleRequestCallback);
    }
}
