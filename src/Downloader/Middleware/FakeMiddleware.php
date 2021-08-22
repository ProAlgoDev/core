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

namespace Sassnowski\Roach\Downloader\Middleware;

use Closure;
use PHPUnit\Framework\Assert;
use Sassnowski\Roach\Downloader\DownloaderMiddlewareInterface;
use Sassnowski\Roach\Http\Request;
use Sassnowski\Roach\Http\Response;

/**
 * @internal
 */
final class FakeMiddleware extends DownloaderMiddleware implements DownloaderMiddlewareInterface
{
    /**
     * @var array Request[]
     */
    private array $requestsHandled = [];

    /**
     * @var array Response[]
     */
    private array $responsesHandled = [];

    public function __construct(private ?Closure $requestHandler = null, private ?Closure $responseHandler = null)
    {
        parent::__construct();
    }

    public function handleRequest(Request $request): Request
    {
        $this->requestsHandled[] = $request;

        if (null !== $this->requestHandler) {
            return ($this->requestHandler)($request);
        }

        return $request;
    }

    public function handleResponse(Response $response): Response
    {
        $this->responsesHandled[] = $response;

        if (null !== $this->responseHandler) {
            return ($this->responseHandler)($response);
        }

        return $response;
    }

    public function assertRequestHandled(Request $request): void
    {
        Assert::assertContains($request, $this->requestsHandled);
    }

    public function assertRequestNotHandled(Request $request): void
    {
        Assert::assertNotContains($request, $this->requestsHandled);
    }

    public function assertNoRequestsHandled(): void
    {
        Assert::assertEmpty($this->requestsHandled);
    }

    public function assertResponseHandled(Response $response): void
    {
        Assert::assertContains($response, $this->responsesHandled);
    }

    public function assertResponseNotHandled(Response $response): void
    {
        Assert::assertNotContains($response, $this->responsesHandled);
    }

    public function assertNoResponseHandled(): void
    {
        Assert::assertEmpty($this->responsesHandled);
    }
}
