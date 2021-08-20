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

namespace Sassnowski\Roach\Parsing;

final class DropResult
{
    private bool $dropped = false;

    public function __invoke(ParseResult $result): ParseResult
    {
        $this->dropped = true;

        return $result;
    }

    public function dropped(): bool
    {
        return $this->dropped;
    }
}
