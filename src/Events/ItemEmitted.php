<?php

declare(strict_types=1);

/**
 * Copyright (c) 2021 Kai Sassnowski
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @see https://github.com/ksassnowski/roach
 */

namespace Sassnowski\Roach\Events;

use Symfony\Contracts\EventDispatcher\Event;

final class ItemEmitted extends Event
{
    public const NAME = 'item.emitted';

    public function __construct(private mixed $item)
    {
    }

    public function getItem(): mixed
    {
        return $this->item;
    }
}
