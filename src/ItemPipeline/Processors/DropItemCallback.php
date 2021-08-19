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

namespace Sassnowski\Roach\ItemPipeline\Processors;

use Closure;
use Sassnowski\Roach\ItemPipeline\ItemInterface;

final class DropItemCallback
{
    public function __construct(private Closure $callback)
    {
    }

    public function __invoke(ItemInterface $item): ItemInterface
    {
        return ($this->callback)($item);
    }
}
