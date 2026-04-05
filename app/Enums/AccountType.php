<?php

namespace App\Enums;

enum AccountType: string
{
    case Buyer = 'buyer';
    case Seller = 'seller';
    case Dealer = 'dealer';
    case Admin = 'admin';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
