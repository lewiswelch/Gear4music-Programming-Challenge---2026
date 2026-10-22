<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Market;

/**
 * The context for a shopper: country, language and currency.
 * These dimensions are intentionally independent; commercial configuration
 * determines valid combinations rather than hard-coded assumptions here.
 */
final readonly class Market
{
    public function __construct(
        public Country $country,
        public Language $language,
        public Currency $currency,
    ) {
    }
}
