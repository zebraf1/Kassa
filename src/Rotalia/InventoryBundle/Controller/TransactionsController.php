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
     * @param null $memberId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function logAction(Request $request, $memberId = null)
    {
        $page = $request->get('page', 1);
        $resultsPerPage = 10;

        $txQuery = TransactionQuery::create()
            ->orderByCreatedAt(\Criteria::DESC);

        $filterForm = $this->createForm(new TransactionFilterForm());

        if ($memberId !== null) {
            $formData = [
                'member' => ['id' => $memberId]
            ];
        } else {
            //Get form data from session when available
            $formData = $request->getSession()->get('purchaseLogFilter');
        }

        $filterForm->setData($formData);

        //Apply filters
        if (!empty($formData['date'])) {
            /** @var \DateTime $date */
            $date = $formData['date'];
            $txQuery->filterByCreatedAt($date->format('Y-m-d 00:00:00'), \Criteria::GREATER_EQUAL);
            $txQuery->filterByCreatedAt($date->format('Y-m-d 23:59:59'), \Criteria::LESS_EQUAL);
        }

        if (!empty($formData['product']['id'])) {
            $txQuery->filterByProductId($formData['product']['id']);
        }

        if (!empty($formData['member']['id'])) {
            $txQuery->filterByMemberId($formData['member']['id']);
        }

        $transactions = $txQuery->paginate($page, $resultsPerPage);

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
                $request->getSession()->remove('purchaseLogFilter');
                $this->setFlashError($request, $filterForm->getErrors(true));
            }
        }

        return $this->redirect($this->generateUrl('RotaliaInventory_purchaseLog'));
    }

    /**
     * @param Request $request
     * @return JSendResponse
     * @throws \PropelException
     */
    public function listAction(Request $request)
    {
        $txQuery = TransactionQuery::create()
            ->orderByCreatedAt(\Criteria::DESC)
        ;

        $txQuery->limit((int)$request->get('limit', 20));
        $transactions = $txQuery->find();

        return $this->render('RotaliaInventoryBundle:Transactions:list.html.twig', [
            'transactions' => $transactions,
        ]);
    }
}
