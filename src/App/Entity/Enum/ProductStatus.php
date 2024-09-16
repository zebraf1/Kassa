<?php

namespace App\Entity\Enum;

enum ProductStatus: string
{
    use EnumValuesTrait;

    case ACTIVE = 'ACTIVE';
    case DISABLED = 'DISABLED';
}
