<?php

namespace Rotalia\APIBundle\Model;

use Rotalia\APIBundle\Model\om\BaseSettingQuery;

class SettingQuery extends BaseSettingQuery
{
    /**
     * @param $conventId
     * @return Setting
     */
    public static function getCurrentCashSetting($conventId)
    {
        $setting = self::create()
            ->filterByObject(Setting::OBJECT_CONVENT)
            ->filterByObjectId($conventId)
            ->filterByReference(Setting::REFERENCE_CURRENT_CASH)
            ->findOneOrCreate()
        ;

        if ($setting->isNew()) {
            $setting->setValue(0);
            $setting->save();
        }

        return $setting;
    }
}
