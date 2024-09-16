<?php

namespace App\Entity\Enum;

enum ProductAmountType: string
{
    use EnumValuesTrait;

    case PIECE = 'PIECE';
    case LITRE = 'LITRE';
    case CENTI_LITRE = 'CENTI_LITRE';
    case KG = 'KG';
    case G = 'G';
}