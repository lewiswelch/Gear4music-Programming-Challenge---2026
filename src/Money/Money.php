<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Money;

use Gear4music\Inventory\Market\Currency;
use InvalidArgumentException;
use NumberFormatter;

/** Immutable monetary value stored as integer minor units. */
final readonly class Money
{
    public function __construct(
        public int $amountInMinorUnits,
        public Currency $currency,
    ) {
        if ($amountInMinorUnits < 0) {
            throw new InvalidArgumentException('Money cannot be negative.');
        }
    }

    /** @return array{amount_minor: int, currency: string} */
    public function toArray(): array
    {
        return [
            'amount_minor' => $this->amountInMinorUnits,
            'currency' => $this->currency->value,
        ];
    }

    /**
     * Format a human-readable price for the supplied locale.
     * The Intl extension is used when available; a predictable fallback keeps
     * the example runnable in minimal PHP installations too.
     */
    public function format(string $locale = 'en-GB'): string
    {
        $digits = $this->currency->minorUnitDigits();
        $amount = $this->amountInMinorUnits / (10 ** $digits);

        if (class_exists(NumberFormatter::class)) {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($amount, $this->currency->value);

            if ($formatted !== false) {
                return $formatted;
            }
        }

        return $this->formatFallback($amount, $digits, $locale);
    }

    private function formatFallback(float $amount, int $digits, string $locale): string
    {
        $usesCommaDecimal = preg_match('/^(de|fr|es|it|nl|sv)(-|$)/i', $locale) === 1;
        $decimalSeparator = $usesCommaDecimal ? ',' : '.';
        $thousandsSeparator = match (strtolower(substr($locale, 0, 2))) {
            'fr', 'sv' => ' ',
            'de', 'es', 'it', 'nl' => '.',
            default => ',',
        };
        $number = number_format($amount, $digits, $decimalSeparator, $thousandsSeparator);

        if ($usesCommaDecimal) {
            $symbol = match ($this->currency) {
                Currency::EUR => '€',
                Currency::GBP => '£',
                Currency::SEK => 'kr',
                Currency::JPY => '¥',
            };
            return $this->currency === Currency::SEK
                ? $number . ' ' . $symbol
                : $number . "\u{00A0}" . $symbol;
        }

        $symbol = match ($this->currency) {
            Currency::GBP => '£',
            Currency::EUR => '€',
            Currency::SEK => 'SEK ',
            Currency::JPY => '¥',
        };

        return $symbol . $number;
    }
}
