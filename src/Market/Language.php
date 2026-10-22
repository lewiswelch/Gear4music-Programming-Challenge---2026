<?php

declare(strict_types=1);

namespace Gear4music\Inventory\Market;

/** ISO 639-1 language codes used by this example. */
enum Language: string
{
    case English = 'en';
    case German = 'de';
    case French = 'fr';
    case Spanish = 'es';
    case Italian = 'it';
    case Dutch = 'nl';
    case Swedish = 'sv';

    public static function default(): self
    {
        return self::English;
    }

    /** A locale suitable for number and currency formatting. */
    public function locale(): string
    {
        return match ($this) {
            self::English => 'en-GB',
            self::German => 'de-DE',
            self::French => 'fr-FR',
            self::Spanish => 'es-ES',
            self::Italian => 'it-IT',
            self::Dutch => 'nl-NL',
            self::Swedish => 'sv-SE',
        };
    }
}
