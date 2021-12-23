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

use Psr\Container\ContainerInterface;
use RoachPHP\Downloader\DownloaderMiddlewareInterface;
use RoachPHP\Downloader\Middleware\DownloaderMiddlewareAdapter;
use RoachPHP\Extensions\ExtensionInterface;
use RoachPHP\ItemPipeline\Processors\ItemProcessorInterface;
use RoachPHP\Spider\Middleware\MiddlewareAdapter;
use RoachPHP\Spider\Middleware\MiddlewareInterface;
use RoachPHP\Spider\SpiderInterface;

final class RunFactory
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function fromSpider(SpiderInterface $spider): Run
    {
        $configuration = $spider->loadConfiguration();

        return new Run(
            $spider->getInitialRequests(),
            $this->buildDownloaderMiddleware($configuration->downloaderMiddleware),
            $this->buildItemPipeline($configuration->itemProcessors),
            $this->buildResponseMiddleware($configuration->spiderMiddleware),
            $this->buildExtensions($configuration->extensions),
            $configuration->concurrency,
            $configuration->requestDelay,
        );
    }

    /**
     * @psalm-param class-string<DownloaderMiddlewareInterface>[] $downloaderMiddleware
     *
     * @return DownloaderMiddlewareInterface[]
     */
    private function buildDownloaderMiddleware(array $downloaderMiddleware): array
    {
        return \array_map(function (string|array $middleware) {
            return new DownloaderMiddlewareAdapter($this->buildConfigurable($middleware));
        }, $downloaderMiddleware);
    }

    /**
     * @psalm-param array<class-string<ItemProcessorInterface>> $processors
     *
     * @return ItemProcessorInterface[]
     */
    private function buildItemPipeline(array $processors): array
    {
        return \array_map([$this, 'buildConfigurable'], $processors);
    }

    /**
     * @psalm-param array<class-string<MiddlewareInterface>> $handlers
     *
     * @return MiddlewareInterface[]
     */
    private function buildResponseMiddleware(array $handlers): array
    {
        return \array_map(function (string|array $handler) {
            return MiddlewareAdapter::fromMiddleware($this->buildConfigurable($handler));
        }, $handlers);
    }

    /**
     * @param array<class-string<ExtensionInterface>> $extensions
     *
     * @return ExtensionInterface[]
     */
    private function buildExtensions(array $extensions): array
    {
        return \array_map(function (string|array $extension) {
            return $this->buildConfigurable($extension);
        }, $extensions);
    }

    /**
     * @template T of \RoachPHP\Support\ConfigurableInterface
     * @psalm-param class-string<T>|array{class-string<T>, array} $configurable
     *
     * @return T
     */
    private function buildConfigurable(string|array $configurable): mixed
    {
        if (!\is_array($configurable)) {
            $configurable = [$configurable, []];
        }

        [$class, $options] = $configurable;

        /** @psalm-var T $instance */
        $instance = $this->container->get($class);
        $instance->configure($options);

        return $instance;
    }
}
