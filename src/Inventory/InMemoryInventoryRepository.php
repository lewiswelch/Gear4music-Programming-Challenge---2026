<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Inventory;

use InvalidArgumentException;

/** In-memory stand-in for the database operations in the starter code. */
final readonly class InMemoryInventoryRepository implements InventoryRepositoryInterface
{
    /** @param list<InventoryInterface> $items */
    public function __construct(private array $items = [])
    {
        foreach ($this->items as $item) {
            if (!$item instanceof InventoryInterface) {
                throw new InvalidArgumentException('Every repository item must implement InventoryInterface.');
            }
        }
    }

    public function findAll(): array
    {
        return $this->items;
    }
}
