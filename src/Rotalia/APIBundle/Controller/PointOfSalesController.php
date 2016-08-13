<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Model\PointOfSaleQuery;
use Rotalia\UserBundle\Model\User;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class PointOfSalesController
 * @package Rotalia\APIBundle\Controller
 */
class PointOfSalesController extends DefaultController
{
    /**
     * Get list of PointOfSales. Super admin will get all, admin will get PointOfSales for his convent.
     *
     * @return JSendResponse
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          403 = "Returned when user does not have admin role",
     *     },
     *     description="Fetch PointOfSales list",
     *     section="PointOfSales",
     * )
     */
    public function listAction()
    {
        if ($this->isGranted(User::ROLE_SUPER_ADMIN)) {
            $pointOfSales = PointOfSaleQuery::create()
                ->find()
            ;
        } elseif ($this->isGranted(User::ROLE_ADMIN)) {
            $pointOfSales = PointOfSaleQuery::create()
                ->filterByConventId($this->getMember()->getKoondisedId())
                ->find()
            ;
        } else {
            throw $this->createAccessDeniedException();
        }

        $result = [
            'pointOfSales' => []
        ];

        foreach ($pointOfSales as $pointOfSale) {
            $result['pointOfSales'][] = $pointOfSale->getAjaxData();
        }

        return JSendResponse::createSuccess($result);
    }

    /**
     * Get info of a PointOfSale
     *
     * @param $id
     * @return JSendResponse
     * @ApiDoc(
     *   resource = true,
     *   section="PointOfSales",
     *   description = "Find PointOfSale for the given ID",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     403 = "Returned when user has insufficient privileges",
     *     404 = "Returned when the PointOfSale is not found for ID",
     *   }
     * )
     */
    public function viewAction($id)
    {
        $pointOfSale = PointOfSaleQuery::create()
            ->findPk($id)
        ;

        if ($pointOfSale === null) {
            return JSendResponse::createFail(['id' => 'Müügikohta ei leitud'], 404);
        }

        return JSendResponse::createSuccess(['pointOfSale' => $pointOfSale->getAjaxData()]);
    }

    public function createAction()
    {
        return JSendResponse::createError(['message' => 'Not implemented'], 501);
    }

    public function deleteAction($id)
    {
        return JSendResponse::createError(['message' => 'Not implemented'], 501);
    }
}
