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

namespace Sassnowski\Roach\Tests\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use PHPUnit\Framework\TestCase;
use Sassnowski\Roach\Http\Response;
use Sassnowski\Roach\Spider\ParseResult;
use Sassnowski\Roach\Tests\InteractsWithRequests;

/**
 * @group http
 *
 * @internal
 */
final class RequestTest extends TestCase
{
    use InteractsWithRequests;

    public function testCanAccessTheRequestUri(): void
    {
        $request = $this->createRequest('::request-uri::');

        self::assertSame('::request-uri::', $request->getUri());
    }

    public function testCanAccessTheRequestUriPath(): void
    {
        $request = $this->createRequest('https://::request-uri::/::path::');

        self::assertSame('/::path::', $request->getPath());
    }

    public function testCanAddHeader(): void
    {
        $request = $this->createRequest();

        self::assertFalse($request->hasHeader('X-Custom-Header'));

        $request->addHeader('X-Custom-Header', '::value::');

        self::assertTrue($request->hasHeader('X-Custom-Header'));
        self::assertSame(['::value::'], $request->getHeader('X-Custom-Header'));
    }

    public function testCanManipulateUnderlyingGuzzleRequest(): void
    {
        $request = $this->createRequest();

        self::assertFalse($request->hasHeader('X-Custom-Header'));

        $request->withGuzzleRequest(static function (Request $guzzleRequest) {
            return $guzzleRequest->withHeader('X-Custom-Header', '::value::');
        });

        self::assertTrue($request->hasHeader('X-Custom-Header'));
        self::assertSame(['::value::'], $request->getHeader('X-Custom-Header'));
    }

    public function testCanCallParseCallback(): void
    {
        $called = false;
        $request = $this->createRequest(callback: static function (Response $response) use (&$called) {
            $called = true;

            yield ParseResult::item(['::item::']);
        });

        $request->callback(
            new Response(new GuzzleResponse(), $request),
        )->next();

        self::assertTrue($called);
    }

    public function testReturnsUnderlyingGuzzleRequest(): void
    {
        $request = $this->createRequest('::request-uri::');

        self::assertSame('::request-uri::', (string) $request->getGuzzleRequest()->getUri());
    }
}
