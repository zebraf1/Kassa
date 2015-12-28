<?php

namespace Rotalia\InventoryBundle\Controller;


use Rotalia\InventoryBundle\Form\TransactionFilterForm;
use Rotalia\InventoryBundle\Model\TransactionQuery;
use Symfony\Component\HttpFoundation\Request;

class TransactionsController extends DefaultController
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
        $reset = $request->get('reset', false);
        $member = $this->getMember();

        $purchaseQuery = TransactionQuery::create()
            ->orderByCreatedAt(\Criteria::DESC);

        $filterForm = $this->createForm(new TransactionFilterForm());

        //Get form data from session when available
        $formData = $request->getSession()->get('purchaseLogFilter');
        if ($formData !== null) {
            $filterForm->setData($formData);
        } else {
            // Reset if not filtered to show current user
            $reset = true;
        }

        // Reset filter form
        if ($reset) {
            $formData = [
                'member' => ['id' => $member->getId()]
            ];
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

        $transactions = $purchaseQuery->paginate($page, $resultsPerPage);

        return $this->render('RotaliaInventoryBundle:Transactions:log.html.twig', [
            'transactions' => $transactions,
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
        $filterForm = $this->createForm(new TransactionFilterForm());
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
