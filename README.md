# Gear4music Inventory Review

A PHP implementation of a market-aware inventory system, with country visibility, language-specific product names, currency-aware pricing and inventory validation.

## Approach and AI Disclosure

I chose approach **(b): a solution guided by third-party tools**.

- **Tool used:** Claude (Anthropic), via claude.ai.
- **How it was used:** Claude helped generate the initial implementation and tests. I reviewed and refined the solution, focusing on validation, currency formatting, edge cases and maintainability.
- **Why:** To explore modern PHP design options and develop a solution within the available time.

## Project Structure

- `src/Market/` — Market, Country, Language and Currency definitions.
- `src/Money/` — Money value object and currency formatting.
- `src/Inventory/` — Inventory interfaces, implementation and visibility rules.
- `src/Indexer/` — Product indexing and market filtering.
- `tests/` — PHPUnit tests covering core behaviour and edge cases.
- `examples/` — Runnable demonstration.

## Key Design Decisions

- **Market configuration:** Country, language and currency are modelled independently to support different market combinations.
- **Type safety:** Enums represent supported countries, languages and currencies.
- **Money handling:** Prices use integer minor units to avoid floating-point errors, with locale-aware formatting where the PHP `intl` extension is available.
- **Language fallback:** Product names fall back to English when a requested translation is unavailable.
- **Country visibility:** Allow-lists and deny-lists control market visibility, with deny rules taking precedence.
- **Validation:** Invalid values, unsupported languages and missing market prices are handled explicitly.
- **Dependency injection:** The indexer depends on an inventory repository interface rather than a concrete implementation.
- **Testing:** Tests cover market filtering, pricing, visibility rules and invalid input.

## Requirements

- PHP 8.2 or later
- Composer
- PHPUnit 11 (installed through Composer)

## Running the Project

Install dependencies:

```bash
composer install
```

Run the test suite:

```bash
composer test
```

Run the example:

```bash
php examples/demo.php
```

## Part 2 — Proposed Extension

Part 2 is a design proposal only and has not been implemented.

To support digital products and additional product conditions, I would introduce:

- **Product types:** Separate physical and digital product behaviour.
- **Interfaces:** Use interfaces such as `ShippableInterface` and `DownloadableInterface` so shipping-specific methods are not required for digital products.
- **Product conditions:** Support new, used and refurbished products through an extensible model.
- **Market-specific rules:** Extend pricing and visibility rules without unnecessarily changing existing inventory behaviour.

This approach would allow the system to grow while keeping responsibilities clear and existing functionality maintainable.