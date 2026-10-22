<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Inventory;

use Gear4music\Inventory\Market\Currency;
use Gear4music\Inventory\Market\Language;
use Gear4music\Inventory\Market\Market;
use Gear4music\Inventory\Money\Money;
use InvalidArgumentException;

final readonly class SimpleInventory implements InventoryInterface
{
    /**
     * @param array<string, string> $names keyed by Language value, e.g. ['en' => 'Guitar']
     * @param array<string, Money> $prices keyed by Currency value, e.g. ['GBP' => Money]
     */
    public function __construct(
        private string $sku,
        private array $names,
        private array $prices,
        private int $weight,
        private int $boxVolume,
        private int $daysToDeliver,
        private int $stockLevel,
        private CountryVisibility $visibility = new CountryVisibility(),
    ) {
        if (trim($this->sku) === '') {
            throw new InvalidArgumentException('SKU cannot be empty.');
        }
        if (!isset($this->names[Language::default()->value]) || trim($this->names[Language::default()->value]) === '') {
            throw new InvalidArgumentException('A non-empty default-language name is required.');
        }
        foreach ($this->names as $language => $name) {
            if (!is_string($language) || Language::tryFrom($language) === null || !is_string($name) || trim($name) === '') {
                throw new InvalidArgumentException('Names must use supported language codes and non-empty strings.');
            }
        }
        foreach ($this->prices as $currencyCode => $price) {
            $currency = is_string($currencyCode) ? Currency::tryFrom($currencyCode) : null;
            if ($currency === null || !$price instanceof Money || $price->currency !== $currency) {
                throw new InvalidArgumentException('Each price key must be a supported currency code matching its Money value.');
            }
        }
        foreach (['weight' => $this->weight, 'box volume' => $this->boxVolume, 'delivery days' => $this->daysToDeliver, 'stock level' => $this->stockLevel] as $field => $value) {
            if ($value < 0) {
                throw new InvalidArgumentException(ucfirst($field) . ' cannot be negative.');
            }
        }
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getName(Market $market): string
    {
        return $this->names[$market->language->value] ?? $this->names[Language::default()->value];
    }

    public function getPrice(Market $market): Money
    {
        return $this->prices[$market->currency->value]
            ?? throw new MissingPriceException(sprintf('%s has no %s price.', $this->sku, $market->currency->value));
    }

    public function isAvailable(): bool
    {
        return $this->stockLevel > 0;
    }

    public function isVisibleIn(Market $market): bool
    {
        return $this->visibility->allows($market->country)
            && isset($this->prices[$market->currency->value]);
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getBoxVolume(): int
    {
        return $this->boxVolume;
    }

    public function getDaysToDeliver(): int
    {
        return $this->daysToDeliver;
    }

    public function getStockLevel(): int
    {
        return $this->stockLevel;
    }
}
