<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
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
        $convents = ConventQuery::create()
            ->filterByIsActive(1)
            ->find()
        ;

        if (!empty($convents)) {
            $convents = array_map(function(Convent $convent) {
                return $convent->getAjaxData();
            }, $convents->getArrayCopy());
        }

        $data = [
            'activeConvents' => $convents
        ];

        return JSendResponse::createSuccess($data);
    }
}