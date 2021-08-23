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

namespace Sassnowski\Roach\Extensions;

use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use Sassnowski\Roach\Events\ItemDropped;
use Sassnowski\Roach\Events\ItemScraped;
use Sassnowski\Roach\Events\RequestDropped;
use Sassnowski\Roach\Events\RequestSending;
use Sassnowski\Roach\Events\RunFinished;
use Sassnowski\Roach\Events\RunStarting;

final class StatsCollectorExtension extends Extension
{
    private DateTimeImmutable $startTime;

    private array $stats = [
        'duration' => null,
        'requests.sent' => 0,
        'requests.dropped' => 0,
        'items.scraped' => 0,
        'items.dropped' => 0,
    ];

    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RunStarting::NAME => ['onRunStarting', 0],
            RequestSending::NAME => ['onRequestSending', 0],
            RequestDropped::NAME => ['onRequestDropped', 0],
            ItemDropped::NAME => ['onItemDropped', 0],
            ItemScraped::NAME => ['onItemScraped', 0],
            RunFinished::NAME => ['onRunFinished', 0],
        ];
    }

    public function onRunStarting(): void
    {
        $this->startTime = new DateTimeImmutable();
    }

    public function onRunFinished(): void
    {
        $duration = $this->startTime->diff(new DateTimeImmutable());

        $this->stats['duration'] = $duration->format('%H:%I:%S');

        $this->logger->info('Run statistics', $this->stats);
    }

    public function onRequestSending(): void
    {
        ++$this->stats['requests.sent'];
    }

    public function onRequestDropped(): void
    {
        ++$this->stats['requests.dropped'];
    }

    public function onItemDropped(): void
    {
        ++$this->stats['items.dropped'];
    }

    public function onItemScraped(): void
    {
        ++$this->stats['items.scraped'];
    }
}
