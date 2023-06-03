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

namespace RoachPHP\Downloader\Middleware;

use RoachPHP\Http\Request;
use RoachPHP\Support\Configurable;

final class ProxyMiddleware implements RequestMiddlewareInterface
{
    use Configurable;

    public function handleRequest(Request $request): Request
    {
        return $request->addOption('proxy', $this->option('proxy'));
    }

    /**
     * @return array{
     *     proxy: string|array{http?: string, https?: string, no?: array<int, string>}
     * }
     */
    private function defaultOptions(): array
    {
        return [
            'proxy' => [],
        ];
    }
}
