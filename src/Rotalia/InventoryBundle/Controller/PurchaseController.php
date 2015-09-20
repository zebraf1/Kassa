<?php

namespace Rotalia\InventoryBundle\Controller;


use Rotalia\InventoryBundle\Form\ProductPurchaseFilterForm;
use Rotalia\InventoryBundle\Model\ProductPurchaseQuery;
use Symfony\Component\HttpFoundation\Request;

class PurchaseController extends DefaultController
{
    /**
     * View purchase log
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logAction(Request $request)
    {
        $page = $request->get('page', 1);
        $resultsPerPage = 10;

        $purchaseQuery = ProductPurchaseQuery::create()
            ->orderByCreatedAt(\Criteria::DESC)
        ;

        $filterForm = $this->createForm(new ProductPurchaseFilterForm());

        //Get form data from session when available
        $formData = $request->getSession()->get('purchaseLogFilter');
        if ($formData !== null) {
            $filterForm->setData($formData);
        }

        //Apply filters
        if (!empty($formData['date'])) {
            /** @var \DateTime $date */
            $date = $formData['date'];
            $purchaseQuery->filterByCreatedAt($date->format('Y-m-d 00:00:00'), \Criteria::GREATER_EQUAL);
            $purchaseQuery->filterByCreatedAt($date->format('Y-m-d 23:59:59'), \Criteria::LESS_EQUAL);
        }

        if (!empty($formData['product']['id'])) {
            $purchaseQuery->filterByProductId($formData['product']['id']);
        }

        if (!empty($formData['member']['id'])) {
            $purchaseQuery->filterByMemberId($formData['member']['id']);
        }

        $productPurchases = $purchaseQuery->paginate($page, $resultsPerPage);

        return $this->render('RotaliaInventoryBundle:Purchase:log.html.twig', [
            'productPurchases' => $productPurchases,
            'filterForm' => $filterForm->createView(),
        ]);
    }


    /**
     * Keeps log url clean and saves search parameters to session
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function logFilterAction(Request $request)
    {
        $filterForm = $this->createForm(new ProductPurchaseFilterForm());
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted()) {
            if ($filterForm->isValid()) {
                $formData = $filterForm->getData();
                $request->getSession()->set('purchaseLogFilter', $formData);
            } else {
                $errors = $filterForm->getErrors(true);
                $request->getSession()->remove('purchaseLogFilter');
                foreach ($errors as $error) {
                    $this->setFlash('error', $error->getMessage());
                }
            }
        }

        return $this->redirect($this->generateUrl('RotaliaInventory_purchaseLog'));
    }
}
