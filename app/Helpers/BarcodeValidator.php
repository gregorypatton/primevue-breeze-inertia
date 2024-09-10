<?php

namespace App\Helpers;

class BarcodeValidator
{
    public static function isValidMod10($code)
    {
        $length = strlen($code);
        $checksum = 0;

        for ($i = 0; $i < $length; $i++) {
            $digit = (int) $code[$length - $i - 1];
            $checksum += $i % 2 === 0 ? $digit : $digit * 3;
        }

        return $checksum % 10 === 0;
    }

    public static function isValidGTIN($gtin)
    {
        $length = strlen($gtin);

        if (!in_array($length, [8, 12, 13, 14])) {
            return false;
        }
        return self::isValidMod10($gtin);
    }
    public static function isValidUPC($upc)
    {
        if (strlen($upc) !== 12) {
            return false;
        }

        return self::isValidMod10($upc);
    }
}
