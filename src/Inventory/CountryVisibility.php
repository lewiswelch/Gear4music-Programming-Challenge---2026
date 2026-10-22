<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Inventory;

use Gear4music\Inventory\Market\Country;
use InvalidArgumentException;

/** Allow-list (empty means everywhere) minus a deny-list; deny always wins. */
final readonly class CountryVisibility
{
    /**
     * @param list<Country> $allowed
     * @param list<Country> $denied
     */
    public function __construct(
        private array $allowed = [],
        private array $denied = [],
    ) {
        foreach (['allowed' => $this->allowed, 'denied' => $this->denied] as $listName => $countries) {
            foreach ($countries as $country) {
                if (!$country instanceof Country) {
                    throw new InvalidArgumentException(sprintf('Every country in %s must be a Country enum.', $listName));
                }
            }
        }
    }

    public static function everywhere(): self
    {
        return new self();
    }

    public function allows(Country $country): bool
    {
        if (in_array($country, $this->denied, true)) {
            return false;
        }

        return $this->allowed === [] || in_array($country, $this->allowed, true);
    }
}
