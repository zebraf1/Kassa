<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseSetting;

class Setting extends BaseSetting
{
    const REFERENCE_CURRENT_CASH = 'currentCash';

    const OBJECT_CONVENT = 'CONVENT';

    /**
     * @param $conventId
     * @return float
     */
    public static function getCurrentCash($conventId)
    {
        $setting = SettingQuery::getCurrentCashSetting($conventId);
        return doubleval($setting->getValue());
    }

    /**
     * @param $conventId
     * @param $value
     */
    public static function setCurrentCash($conventId, $value)
    {
        $setting = SettingQuery::getCurrentCashSetting($conventId);
        $setting->setValue(doubleval($value));
        $setting->save();
    }
}
