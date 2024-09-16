<?php

namespace App\Entity\Enum;

trait EnumValuesTrait
{
    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $case) {
            $values[$case->name] = $case->value;
        }

        return $values;
    }
}