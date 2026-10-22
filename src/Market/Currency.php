<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Market;

/** ISO 4217 currency codes used by this example. */
enum Currency: string
{
    case GBP = 'GBP';
    case EUR = 'EUR';
    case SEK = 'SEK';
    case JPY = 'JPY';

    /** Number of minor-unit decimal places for this currency. */
    public function minorUnitDigits(): int
    {
        return match ($this) {
            self::JPY => 0,
            default => 2,
        };
    }
}
