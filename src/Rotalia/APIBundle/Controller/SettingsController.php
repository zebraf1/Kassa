<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\APIBundle\Model\Setting;
use Rotalia\APIBundle\Model\SettingQuery;
use Rotalia\UserBundle\Model\Convent;
use Rotalia\UserBundle\Model\ConventQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc; // Used for API documentation

/**
 * Class SettingsController
 * @package Rotalia\APIBundle\Controller
 */
class SettingsController extends DefaultController
{
    /**
     * Get all global and user settings
     *
     * @ApiDoc(
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          403 = "Returned when user is not authenticated",
     *     },
     *     description="Fetch settings list",
     *     section="Settings",
     * )
     * @return JSendResponse
     */
    public function listAction()
    {
        /** @var Convent[] $convents */
        $convents = ConventQuery::create()
            ->filterByIsActive(1)
            ->find()
        ;

        /** @var Setting[] $conventSettings */
        $conventSettings = SettingQuery::create()
            ->filterByObject(Setting::OBJECT_CONVENT)
            ->find();

        foreach ($conventSettings as $setting) {
            $settings[$setting->getObjectId()][$setting->getReference()] = $setting->getValue();
        }

        $conventData = [];

        foreach ($convents as $convent) {
            $conventData[] = [
                'id' => $convent->getId(),
                'name' => $convent->getName(),
                'settings' => isset($settings[$convent->getId()]) ? $settings[$convent->getId()] : new \stdClass(),
            ];
        }

        $data = [
            'activeConvents' => $conventData,
        ];

        return JSendResponse::createSuccess($data);
    }
}
