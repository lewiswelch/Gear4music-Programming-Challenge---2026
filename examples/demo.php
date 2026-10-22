<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Gear4music\Inventory\Indexer\ProductIndexer;
use Gear4music\Inventory\Inventory\CountryVisibility;
use Gear4music\Inventory\Inventory\InMemoryInventoryRepository;
use Gear4music\Inventory\Inventory\SimpleInventory;
use Gear4music\Inventory\Market\Country;
use Gear4music\Inventory\Market\Currency;
use Gear4music\Inventory\Market\Language;
use Gear4music\Inventory\Money\Money;

$indexer = new ProductIndexer(new InMemoryInventoryRepository([
    new SimpleInventory(
        'GTR-001',
        ['en' => 'Electric Guitar', 'de' => 'E-Gitarre'],
        ['GBP' => new Money(19999, Currency::GBP), 'EUR' => new Money(23999, Currency::EUR)],
        3500,
        120,
        2,
        10,
    ),
    new SimpleInventory(
        'AMP-UK',
        ['en' => 'UK-only Amp'],
        ['GBP' => new Money(14999, Currency::GBP)],
        9000,
        200,
        3,
        4,
        new CountryVisibility([Country::GB]),
    ),
]));

$documents = $indexer->getInventory('DE', 'de-DE', 'EUR');

echo json_encode($documents, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
