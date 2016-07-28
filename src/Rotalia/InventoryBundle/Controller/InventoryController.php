<?php

namespace Rotalia\InventoryBundle\Controller;

use Rotalia\InventoryBundle\Form\ProductListType;
use Rotalia\InventoryBundle\Form\ProductType;
use Rotalia\InventoryBundle\Model\Form\ProductList;
use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;

use Symfony\Component\HttpFoundation\Request;

class InventoryController extends DefaultController
{
    /**
     * Display Product categories and its products
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $this->requireAdmin();

        /** @var Product[] $products */
        $products = ProductQuery::create()
            ->orderBySeq()
            ->find();

        $form = $this->createForm(new ProductListType(), new ProductList($products));

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var Product[] $products */
                $products = $form->getData();
                foreach($products as $product) {
                    $product->save();
                }
                return $this->redirect($this->generateUrl('RotaliaInventory_list'));
            } else {
                $this->setFlashError($request,'Järjekorra salvestamine ebaõnnestus');
            }
        }

        $productsIndexed = [];
        $conventId = $this->getMember()->getKoondisedId();

        foreach ($products as $product) {
            $product->setConventId($conventId);
            $productsIndexed[$product->getId()] = $product;
        }

        return $this->render('RotaliaInventoryBundle:Inventory:list.html.twig', array(
            'products' => $productsIndexed,
            'form' => $form->createView(),
        ));
    }

    /**
     * @param Request $request
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     * @throws \PropelException
     */
    public function editAction(Request $request, $id = null)
    {
        $this->requireAdmin();

        if ($id) {
            /** @var Product $product */
            $product = ProductQuery::create()->findPk($id);
            if (! $product) {
                throw $this->createNotFoundException('Toodet ei leitud:'. $id);
            }
        } else {
            $product = new Product();
            $maxSeqProduct = ProductQuery::create()
                ->orderBySeq(\Criteria::DESC)
                ->findOne();
            $seq = $maxSeqProduct ? $maxSeqProduct->getSeq() + 1 : 1;
            $product->setSeq($seq);
        }

        $product->setConventId($this->getMember()->getKoondisedId());

        $form = $this->createForm(new ProductType(), $product);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $product = $form->getData();
                $product->ensureProductInfos();
                $product->save();
                return $this->redirect($this->generateUrl('RotaliaInventory_list'));
            }
        }

        return $this->render('RotaliaInventoryBundle:Inventory:productEdit.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
