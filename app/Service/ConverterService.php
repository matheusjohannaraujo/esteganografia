<?php

namespace App\Service;
        
class ConverterService
{
    
    public static function decToBin(int $dec) :string
    {
        return str_pad(decbin($dec), 8, '0', STR_PAD_LEFT);
    }

    public static function binToDec(string $bin) :int
    {
        return bindec($bin);
    }

    public static function charToBin(string $char) :string
    {
        return self::decToBin(ord($char));
    }

    public static function binToChar(string $bin) :string
    {
        return chr(self::binToDec($bin));
    }

}
