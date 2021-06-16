<?php

namespace Rotalia\InventoryBundle\Controller;

use Rotalia\InventoryBundle\Form\PointOfSaleType;
use Rotalia\APIBundle\Model\PointOfSale;
use Rotalia\APIBundle\Model\PointOfSaleQuery;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PointOfSaleController
 * @package Rotalia\InventoryBundle\Controller
 */
class PointOfSaleController extends DefaultController
{
    public function listAction(Request $request)
    {
        $this->requireAdmin();

        $pointsOfSale = PointOfSaleQuery::create()->orderByCreatedBy(\Criteria::DESC)->find();

        $pos = null;

        $hash = $request->cookies->get('pos_hash');
        if ($hash) {
            $pos = PointOfSaleQuery::create()->findOneByHash($hash);
        }

        if ($pos === null) {
            $pos = new PointOfSale();
        }

        $form = $this->createForm(new PointOfSaleType($pos->isNew()), $pos, [
            'action' => $this->generateUrl('RotaliaInventory_pointOfSaleAdd'),
            'method' => 'POST',
        ]);

        return $this->render('RotaliaInventoryBundle:PoS:list.html.twig', [
            'pointsOfSale' => $pointsOfSale,
            'form' => $form->createView(),
            'pos' => $pos,
        ]);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function addAction(Request $request)
    {
        $this->requireAdmin();

        $pos = null;

        $hash = $request->cookies->get('pos_hash');
        if ($hash) {
            $pos = PointOfSaleQuery::create()->findOneByHash($hash);
        } else {
            $hash = md5($request->getClientIp() . time());
        }

        if ($pos === null) {
            $pos = new PointOfSale();
            $pos->setMember($this->getMember());
            $pos->setHash($hash);
        }

        $isNew = $pos->isNew();
        $pos->setDeviceInfo($request->headers->get('User-Agent'));

        $form = $this->createForm(new PointOfSaleType($isNew), $pos);
        $form->handleRequest($request);

        $response = $this->redirect($this->generateUrl('RotaliaInventory_pointOfSaleList'));

        if ($form->isValid()) {
            $pos = $form->getData();
            $pos->save();

            $this->setFlashOk($request, $isNew ? 'Kassa lisatud' : 'Nimi muudetud');
            //Cookie should be refreshed every time it is used
            $response->headers->setCookie(new Cookie('pos_hash', $hash, new \DateTime('+1 year')));
        } else {
            $this->setFlashError($request, $form->getErrors());
        }

        return $response;
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Exception
     * @throws \PropelException
     */
    public function deleteAction(Request $request, $id)
    {
        $this->requireAdmin();

        $pos = PointOfSaleQuery::create()->findPk($id);

        if (!$pos) {
            $this->setFlashError($request, 'Kassat ei leitud');
        } else {
            $pos->delete();
            $this->setFlashOk($request, 'Kassa kustutatud');
        }

        return $this->redirect($this->generateUrl('RotaliaInventory_pointOfSaleList'));
    }
}
