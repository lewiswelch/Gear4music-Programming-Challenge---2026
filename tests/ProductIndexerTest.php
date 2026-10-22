<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Tests;

use Gear4music\Inventory\Indexer\ProductIndexer;
use Gear4music\Inventory\Inventory\CountryVisibility;
use Gear4music\Inventory\Inventory\InMemoryInventoryRepository;
use Gear4music\Inventory\Inventory\MissingPriceException;
use Gear4music\Inventory\Inventory\SimpleInventory;
use Gear4music\Inventory\Market\Country;
use Gear4music\Inventory\Market\Currency;
use Gear4music\Inventory\Market\Language;
use Gear4music\Inventory\Market\Market;
use Gear4music\Inventory\Money\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductIndexerTest extends TestCase
{
    /** @param array<string, Money> $prices */
    private function item(string $sku, CountryVisibility $visibility, array $prices, int $stock = 5): SimpleInventory
    {
        return new SimpleInventory($sku, ['en' => 'Guitar', 'de' => 'Gitarre'], $prices, 3000, 90, 2, $stock, $visibility);
    }

    public function testFiltersByCountryAndCurrency(): void
    {
        $gbp = ['GBP' => new Money(19999, Currency::GBP)];
        $both = $gbp + ['EUR' => new Money(23999, Currency::EUR)];
        $indexer = new ProductIndexer(new InMemoryInventoryRepository([
            $this->item('UK-ONLY', new CountryVisibility([Country::GB]), $both),
            $this->item('NOT-FR', new CountryVisibility([], [Country::FR]), $both),
            $this->item('NO-EUR', CountryVisibility::everywhere(), $gbp),
            $this->item('EVERYWHERE', CountryVisibility::everywhere(), $both),
        ]));

        $documents = $indexer->getInventory('DE', 'de-DE', 'EUR');
        $skus = array_column($documents, 'id');

        self::assertSame(['NOT-FR', 'EVERYWHERE'], $skus);
        self::assertSame('Gitarre', $documents[0]['name']);
        self::assertSame(['amount_minor' => 23999, 'currency' => 'EUR'], $documents[0]['price']);
    }

    public function testDenyListWinsOverAllowList(): void
    {
        $visibility = new CountryVisibility([Country::GB, Country::FR], [Country::FR]);

        self::assertTrue($visibility->allows(Country::GB));
        self::assertFalse($visibility->allows(Country::FR));
        self::assertFalse($visibility->allows(Country::DE));
    }

    public function testEmptyVisibilityListsAllowEveryCountry(): void
    {
        self::assertTrue(CountryVisibility::everywhere()->allows(Country::SE));
    }

    public function testNameFallsBackToDefaultLanguage(): void
    {
        $item = $this->item('X', CountryVisibility::everywhere(), ['EUR' => new Money(1, Currency::EUR)]);

        self::assertSame('Gitarre', $item->getName(new Market(Country::DE, Language::German, Currency::EUR)));
        self::assertSame('Guitar', $item->getName(new Market(Country::FR, Language::French, Currency::EUR)));
    }

    public function testMissingPriceThrowsDomainException(): void
    {
        $item = $this->item('X', CountryVisibility::everywhere(), ['GBP' => new Money(100, Currency::GBP)]);

        $this->expectException(MissingPriceException::class);
        $item->getPrice(new Market(Country::DE, Language::German, Currency::EUR));
    }

    public function testAvailabilityDependsOnStockLevel(): void
    {
        $prices = ['GBP' => new Money(100, Currency::GBP)];

        self::assertTrue($this->item('IN-STOCK', CountryVisibility::everywhere(), $prices, 1)->isAvailable());
        self::assertFalse($this->item('OUT-OF-STOCK', CountryVisibility::everywhere(), $prices, 0)->isAvailable());
    }

    public function testRejectsNegativeStock(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SimpleInventory('X', ['en' => 'Guitar'], ['GBP' => new Money(100, Currency::GBP)], 1, 1, 1, -1);
    }

    public function testRejectsUnsupportedTranslationLanguage(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SimpleInventory('X', ['en' => 'Guitar', 'xx' => 'Unknown'], ['GBP' => new Money(100, Currency::GBP)], 1, 1, 1, 1);
    }

    public function testRejectsPriceWhoseCurrencyDoesNotMatchItsKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new SimpleInventory('X', ['en' => 'Guitar'], ['GBP' => new Money(100, Currency::EUR)], 1, 1, 1, 1);
    }

    public function testMoneyFormatsCurrencyAndMinorUnits(): void
    {
        $pounds = (new Money(34900, Currency::GBP))->format('en-GB');
        $euros = (new Money(34900, Currency::EUR))->format('de-DE');
        $yen = (new Money(349, Currency::JPY))->format('en-GB');

        self::assertStringContainsString('349', $pounds);
        self::assertStringContainsString('£', $pounds);
        self::assertStringContainsString('349', $euros);
        self::assertStringContainsString('€', $euros);
        self::assertStringContainsString('349', $yen);
        self::assertStringContainsString('¥', $yen);
    }

    public function testMoneyCannotBeNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Money(-1, Currency::GBP);
    }
}
