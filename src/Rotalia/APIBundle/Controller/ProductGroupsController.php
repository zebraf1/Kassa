<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Model\ProductGroup;
use Rotalia\InventoryBundle\Model\ProductGroupQuery;
use Nelmio\ApiDocBundle\Annotation\ApiDoc; // Used for API documentation

class ProductGroupsController extends DefaultController
{
    /**
     * Fetch all ProductGroup objects (id, name, seq), ordered by seq.
     *
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          403 = "Returned when user is not authenticated",
     *     },
     *     description="Fetch Product Group list",
     *     section="ProductGroups",
     * )
     *
     * @return JSendResponse
     */
    public function listAction()
    {
        /** @var ProductGroup[] $productGroups */
        $productGroups = ProductGroupQuery::create()
            ->orderBySeq()
            ->find()
        ;

        $result = [
            'productGroups' => []
        ];

        foreach ($productGroups as $productGroup) {
            $result['productGroups'][] = $productGroup->getAjaxData();
        }

        return JSendResponse::createSuccess($result);
    }
}
