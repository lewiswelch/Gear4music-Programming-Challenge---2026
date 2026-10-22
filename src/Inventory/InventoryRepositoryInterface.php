<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Inventory;

interface InventoryRepositoryInterface
{
    /** @return list<InventoryInterface> */
    public function findAll(): array;
}
