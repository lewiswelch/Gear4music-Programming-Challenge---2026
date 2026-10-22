<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Inventory;

use Gear4music\Inventory\Market\Market;
use Gear4music\Inventory\Money\Money;

interface InventoryInterface
{
    public function getSku(): string;

    /** Return the requested translation, falling back to the default language. */
    public function getName(Market $market): string;

    /** @throws MissingPriceException when no price exists for this market currency. */
    public function getPrice(Market $market): Money;

    public function isAvailable(): bool;

    /** Includes country visibility and the existence of a market-currency price. */
    public function isVisibleIn(Market $market): bool;

    public function getWeight(): int;

    public function getBoxVolume(): int;

    public function getDaysToDeliver(): int;

    public function getStockLevel(): int;
}
