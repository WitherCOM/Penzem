<?php

namespace App\Support;

class CurrencyConverter
{
    protected static array $defaultConversionTable = [
        'HUF' => 1,
        'EUR' => 395,
        'CZK' => 16
    ];

    public static function convert(string $out_currency, string $in_currency, $amount)
    {
        if (!array_key_exists($out_currency, self::$defaultConversionTable) || !array_key_exists($in_currency, self::$defaultConversionTable))
        {
            return 0;
        }
        else
        {
            return self::$defaultConversionTable[$out_currency] / self::$defaultConversionTable[$in_currency] * $amount;
        }
    }
}
