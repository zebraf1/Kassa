<?php

namespace Rotalia\InventoryBundle\Classes;

class XClassifier
{
    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_DISABLED = 'DISABLED';

    public static $STATUSES = [
        self::STATUS_ACTIVE => 'Aktiivne',
        self::STATUS_DISABLED => 'Inaktiivne',
    ];

    const AMOUNT_TYPE = 'AMOUNT_TYPE';
    const AMOUNT_TYPE_PIECE = 'PIECE';
    const AMOUNT_TYPE_LITRE = 'LITRE';
    const AMOUNT_TYPE_KG = 'KG';

    public static $AMOUNT_TYPES = [
        self::AMOUNT_TYPE_PIECE => 'Tükk',
        self::AMOUNT_TYPE_LITRE => 'Liiter',
        self::AMOUNT_TYPE_KG => 'Kilogramm',
    ];

    const CONVENT_TALLINN_ID = 6;
}
