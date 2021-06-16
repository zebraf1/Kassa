<?php

namespace Rotalia\APIBundle\Classes;

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
    const AMOUNT_TYPE_CENTI_LITRE = 'CENTI_LITRE';
    const AMOUNT_TYPE_KG = 'KG';
    const AMOUNT_TYPE_G = 'G';

    public static $AMOUNT_TYPES = [
        self::AMOUNT_TYPE_PIECE => 'Tükk',
        self::AMOUNT_TYPE_LITRE => 'Liiter',
        self::AMOUNT_TYPE_CENTI_LITRE => 'Sentiliiter',
        self::AMOUNT_TYPE_KG => 'Kilogramm',
        self::AMOUNT_TYPE_G => 'Gramm',
    ];

    const RESOURCE_TYPE_LIMITED = 'LIMITED';
    const RESOURCE_TYPE_UNLIMITED = 'UNLIMITED';

    public static $RESOURCE_TYPES = [
        self::RESOURCE_TYPE_LIMITED => 'Piiratud',
        self::RESOURCE_TYPE_UNLIMITED => 'Lõputu',
    ];

    const CONVENT_TALLINN_ID = 6;
}
