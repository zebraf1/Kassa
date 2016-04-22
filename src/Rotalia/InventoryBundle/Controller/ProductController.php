<?php

namespace Rotalia\InventoryBundle\Controller;


use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\InventoryBundle\Model\ProductQuery;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductController
 * @package Rotalia\InventoryBundle\Controller
 */
class ProductController extends DefaultController
{
    /**
     * @param Request $request
     * @return JSendResponse
     */
    public function infoAction(Request $request)
    {
        $active = $request->get('active', null);

        $productIds = $request->get('product_ids');
        $products = ProductQuery::create()
            ->filterByStatusId(XClassifier::STATUS_ACTIVE)
            ->filterByActiveStatus($active)
            ->findPks($productIds);

        $result = [];
        foreach ($products as $product) {
            $result[] = [
                'id' => $product->getId(),
                'price' => $product->getPrice(),
                'text' => $product->getAjaxName(),
            ];
        }

        return JSendResponse::createSuccess($result);
    }
}
