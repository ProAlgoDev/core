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

namespace RoachPHP\Core;

use RoachPHP\Downloader\DownloaderMiddlewareInterface;
use RoachPHP\Http\Request;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\ResponseProcessing\MiddlewareInterface;

final class Run
{
    /**
     * @param Request[]                       $startRequests
     * @param DownloaderMiddlewareInterface[] $downloaderMiddleware
     * @param ItemProcessorInterface[]        $itemProcessors
     * @param MiddlewareInterface[]           $responseMiddleware
     */
    public function __construct(
        public array $startRequests = [],
        public array $downloaderMiddleware = [],
        public array $itemProcessors = [],
        public array $responseMiddleware = [],
        public int $concurrency = 25,
        public int $requestDelay = 0,
    ) {
    }
}
