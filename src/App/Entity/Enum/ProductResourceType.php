<?php

namespace App\Entity\Enum;

enum ProductResourceType: string
{
    use EnumValuesTrait;

    case LIMITED = 'LIMITED';
    case UNLIMITED = 'UNLIMITED';
}
