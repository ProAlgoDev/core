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

use Generator;
use PHPUnit\Framework\TestCase;
use Sassnowski\Roach\Downloader\Middleware\DownloaderMiddleware;
use Sassnowski\Roach\Downloader\Middleware\DownloaderMiddlewareAdapter;
use Sassnowski\Roach\Downloader\Middleware\RequestMiddlewareInterface;
use Sassnowski\Roach\Downloader\Middleware\ResponseMiddlewareInterface;
use Sassnowski\Roach\Http\Request;
use Sassnowski\Roach\Http\Response;
use Sassnowski\Roach\Tests\InteractsWithRequestsAndResponses;

/**
 * @internal
 */
final class MiddlewareAdapaterTest extends TestCase
{
    use InteractsWithRequestsAndResponses;

    /**
     * @dataProvider requestMiddlewareProvider
     */
    public function testRequestMiddlewareImplementation(callable $testCase): void
    {
        $middleware = new class() extends DownloaderMiddleware implements RequestMiddlewareInterface {
            public function handleRequest(Request $request): Request
            {
                return $request->withMeta('::key::', '::value::');
            }
        };
        $adapter = new DownloaderMiddlewareAdapter($middleware);

        $testCase($adapter);
    }

    public function requestMiddlewareProvider(): Generator
    {
        yield 'return response unchanged' => [function (DownloaderMiddlewareAdapter $adapter): void {
            $response = $this->makeResponse();

            $result = $adapter->handleResponse($response);

            self::assertSame($response, $result);
        }];

        yield 'call middleware for requests' => [function (DownloaderMiddlewareAdapter $adapter): void {
            $request = $this->makeRequest();

            $result = $adapter->handleRequest($request);

            self::assertSame('::value::', $result->getMeta('::key::'));
        }];
    }

    /**
     * @dataProvider responseMiddlewareProvider
     */
    public function testResponseMiddlewareImplementation(callable $testCase): void
    {
        $middleware = new class() extends DownloaderMiddleware implements ResponseMiddlewareInterface {
            public function handleResponse(Response $response): Response
            {
                return $response->withMeta('::key::', '::value::');
            }
        };
        $adapter = new DownloaderMiddlewareAdapter($middleware);

        $testCase($adapter);
    }

    public function responseMiddlewareProvider(): Generator
    {
        yield 'return request unchanged' => [function (DownloaderMiddlewareAdapter $adapter): void {
            $request = $this->makeRequest();

            $result = $adapter->handleRequest($request);

            self::assertSame($request, $result);
        }];

        yield 'call middleware for responses' => [function (DownloaderMiddlewareAdapter $adapter): void {
            $response = $this->makeResponse();

            $result = $adapter->handleResponse($response);

            self::assertSame('::value::', $result->getMeta('::key::'));
        }];
    }
}
