<?php

namespace Rotalia\InventoryBundle\Controller;

use Rotalia\InventoryBundle\Classes\OutOfStockException;
use Rotalia\InventoryBundle\Form\ProductListType;
use Rotalia\InventoryBundle\Form\ProductType;
use Rotalia\InventoryBundle\Model\Form\ProductList;
use Rotalia\InventoryBundle\Model\Product;
use Rotalia\InventoryBundle\Model\ProductQuery;

use Rotalia\UserBundle\Model\SessionQuery;
use Symfony\Component\HttpFoundation\Request;

class InventoryController extends DefaultController
{
    /**
     * Display Product categories and its products
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
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

        foreach ($products as $product) {
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
     * @return \Symfony\Component\HttpFoundation\Response|static
     * @throws \Exception
     * @throws \PropelException
     */
    public function editAction(Request $request, $id = null)
    {
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

        $form = $this->createForm(new ProductType(), $product);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $product = $form->getData();
                $product->save();
                return $this->redirect($this->generateUrl('RotaliaInventory_list'));
            }
        }

        return $this->render('RotaliaInventoryBundle:Inventory:productEdit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function buyItemAction($id)
    {
        $product = ProductQuery::create()->findPk($id);
        if (!$product)
        {
            throw $this->createNotFoundException('Toodet ei leitud');
        }

        try
        {
            $product->reduceAmount();
            $product->save();
        }
        catch (OutOfStockException $e)
        {
            $this->get('session')->setFlash('error', 'Toode on otsas');
            return $this->redirect($this->generateUrl('RotaliaInventory_list'));
        }

        return $this->render('RotaliaInventoryBundle:Inventory:buyItem.html.twig', array('product' => $product));
    }

    public function addItemAction($id)
    {
        $product = ProductQuery::create()->findPk($id);
        if (!$product)
        {
            throw $this->createNotFoundException('Toodet ei leitud');
        }
        $product->increaseAmount();
        $product->save();
        return $this->render('RotaliaInventoryBundle:Inventory:addItem.html.twig', array('product' => $product));
    }
}
