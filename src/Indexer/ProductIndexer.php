<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Indexer;

use Gear4music\Inventory\Inventory\InventoryInterface;
use Gear4music\Inventory\Inventory\InventoryRepositoryInterface;
use Gear4music\Inventory\Market\Country;
use Gear4music\Inventory\Market\Currency;
use Gear4music\Inventory\Market\Language;
use Gear4music\Inventory\Market\Market;
use InvalidArgumentException;

final readonly class ProductIndexer
{
    public function __construct(private InventoryRepositoryInterface $repository)
    {
    }

    /**
     * Build search-index documents for the requested country, locale and currency.
     *
     * @return list<array<string, mixed>>
     */
    public function getInventory(string $countryCode, string $locale, string $currencyCode): array
    {
        $country = Country::tryFrom(strtoupper($countryCode));
        $currency = Currency::tryFrom(strtoupper($currencyCode));
        $language = Language::tryFrom(strtolower(substr($locale, 0, 2)));

        if ($country === null) {
            throw new InvalidArgumentException(sprintf('Unsupported country code: %s', $countryCode));
        }
        if ($currency === null) {
            throw new InvalidArgumentException(sprintf('Unsupported currency code: %s', $currencyCode));
        }
        if ($language === null) {
            throw new InvalidArgumentException(sprintf('Unsupported locale: %s', $locale));
        }

        $market = new Market($country, $language, $currency);
        $documents = [];

        foreach ($this->repository->findAll() as $product) {
            if (!$product->isVisibleIn($market)) {
                continue;
            }

            $documents[] = [
                'id' => $product->getSku(),
                'name' => $product->getName($market),
                'price' => $product->getPrice($market)->toArray(),
                'available' => $product->isAvailable(),
                'weight' => $product->getWeight(),
                'box_volume' => $product->getBoxVolume(),
                'days_to_deliver' => $product->getDaysToDeliver(),
                'stock_level' => $product->getStockLevel(),
                'country' => strtoupper($countryCode),
                'locale' => $locale,
            ];
        }

        return $documents;
    }
}
