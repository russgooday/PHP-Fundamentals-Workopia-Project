<?php
namespace App\Support;
use NumberFormatter;

class Currency {
    protected static string $currency = 'USD';
    protected static string $locale = 'en-US';

    public static function format(int|float $amount, ?int $precision = NULL): string {
        $formatter = new NumberFormatter(static::$locale, NumberFormatter::CURRENCY);

        if (!is_null($precision))
            $formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $precision);

        return $formatter->formatCurrency($amount, static::$currency);
    }

    public static function setCurrency(string $currency, string $locale): void {
        static::$currency = $currency;
        static::$locale = $locale;
    }
}