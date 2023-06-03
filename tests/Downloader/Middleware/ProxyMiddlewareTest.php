<?php

declare(strict_types=1);

/**
 * Copyright (c) 2023 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/roach-php/roach
 */

namespace RoachPHP\Tests\Downloader\Middleware;

use PHPUnit\Framework\TestCase;
use RoachPHP\Downloader\Middleware\ProxyMiddleware;
use RoachPHP\Testing\Concerns\InteractsWithRequestsAndResponses;

/**
 * @internal
 */
final class ProxyMiddlewareTest extends TestCase
{
    use InteractsWithRequestsAndResponses;

    public function testDontUseAProxyByDefault(): void
    {
        $middleware = new ProxyMiddleware();

        $request = $middleware->handleRequest($this->makeRequest());

        self::assertSame([], $request->getOptions()['proxy']);
    }

    public function testUseProxyForAllProtocols(): void
    {
        $middleware = new ProxyMiddleware();
        $middleware->configure([
            'proxy' => '::proxy::',
        ]);

        $request = $middleware->handleRequest($this->makeRequest());

        self::assertSame('::proxy::', $request->getOptions()['proxy']);
    }

    public function testSpecifyDifferentProxiesForDifferentProtocols(): void
    {
        $middleware = new ProxyMiddleware();
        $middleware->configure([
            'proxy' => [
                'http' => '::http-proxy::',
                'https' => '::https-proxy::',
            ],
        ]);

        $request = $middleware->handleRequest($this->makeRequest());

        self::assertSame([
            'http' => '::http-proxy::',
            'https' => '::https-proxy::',
        ], $request->getOptions()['proxy']);
    }

    public function testDefineDomainsThatShouldNotUseProxies(): void
    {
        $middleware = new ProxyMiddleware();
        $middleware->configure([
            'proxy' => [
                'http' => '::http-proxy::',
                'no' => ['::domain-1::', '::domain-2::'],
            ],
        ]);

        $request = $middleware->handleRequest($this->makeRequest());

        self::assertSame([
            'http' => '::http-proxy::',
            'no' => ['::domain-1::', '::domain-2::'],
        ], $request->getOptions()['proxy']);
    }
}
