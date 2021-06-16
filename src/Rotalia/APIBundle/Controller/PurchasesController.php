<?php

namespace Rotalia\APIBundle\Controller;

use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\APIBundle\Model\Transaction;
use Rotalia\APIBundle\Model\TransactionQuery;
use Rotalia\UserBundle\Model\MemberQuery;
use Rotalia\UserBundle\Model\User;
use Symfony\Component\HttpFoundation\Request;
use DateTime;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class PurchasesController extends DefaultController
{

    /**
     * Fetch list of purchases
     *
     * @param Request $request
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          400 = "Returned when dateFrom or dateUntil are not in valid date format",
     *          403 = "Returned when user has insufficient privileges",
     *     },
     *     description="Fetch purchases list",
     *     section="Purchase",
     *     filters={
     *          {"name"="conventId","type"="int","description"="Fetch purchases for another convent than member home convent"},
     *          {"name"="memberId","dataType"="integer","description"="Member ID of user in interest"},
     *          {"name"="memberName","dataType"="string","description"="Part of the name of the payer"},
     *          {"name"="createdByName","dataType"="string","description"="Part of the name of the buyer"},
     *          {"name"="dateFrom","type"="string","description":"datetime string"},
     *          {"name"="dateUntil","type"="string","description":"datetime string"},
     *          {"name"="limit","type"="int","description":"Limit number of purchases returned, default 5"},
     *          {"name"="offset","type"="int","description":"Offset purchases returned for pagination, default 0"},
     *     }
     * )
     *
     * @return JSendResponse
     */
    public function listAction(Request $request)
    {
        $conventId = $request->get('conventId', null);
        $memberId = $request->get('memberId', null);
        $memberName = $request->get('memberName');
        $createdByName = $request->get('createdByName');
        $dateFrom = $request->get('dateFrom', null);
        $dateUntil = $request->get('dateUntil', null);
        $limit = $request->get('limit', 5);
        $offset = $request->get('offset', 0);

        $purchaseQuery = TransactionQuery::create()
            ->filterByType(Transaction::TYPE_CREDIT_PURCHASE)
        ;


        if ($this->getMember() === null) {
            // Get Point of Sale
            $pos = $this->getPos($request);

            if ($pos == null) {
                return JSendResponse::createFail("Ostude nägemiseks peab olema kas sisse logitud või kasutama müügipunkti", 403);
            }

            if ($conventId !== null && $conventId != $pos->getConventId()) {
                return JSendResponse::createFail('Müügipunktist näeb ainult sama konvendi oste', 403);
            }

            if ($memberId !== null) {
                return JSendResponse::createFail('Müügipunktist ei saa vaadata üksiku kasutaja oste', 403);
            }

            $purchaseQuery->filterByConventId($pos->getConventId());

            // Set other filtering by hand
            $dateFrom = new DateTime();
            $dateFrom->modify('-1 hour');
            $dateFrom = $dateFrom->format('Y-m-d H:i:s');
            $dateUntil = null;
            $limit = null;
            $offset = 0;

        } else {
            // Authenticated user

            $memberConventId = $this->getMember()->getKoondisedId();

            if ($memberId == $this->getMember()->getId()) {
                // User requests its own purchases
                $purchaseQuery->filterByEitherMemberId($memberId);

                if ($conventId !== null) {
                    $purchaseQuery->filterByConventId($conventId);
                }
            } elseif ($conventId == $memberConventId) {
                if ($this->isGranted(User::ROLE_ADMIN)) {
                    $purchaseQuery->filterByConventId($conventId);

                    if ($memberId !== null) {
                        $purchaseQuery->filterByEitherMemberId($memberId);
                    }
                } else {
                    return JSendResponse::createFail('Ainult admin saab pärida teiste oste', 403);
                }
            } else {
                if ($this->isGranted(User::ROLE_SUPER_ADMIN)) {

                    if ($conventId !== null) {
                        $purchaseQuery->filterByConventId($conventId);
                    }

                    if ($memberId !== null) {
                        $purchaseQuery->filterByEitherMemberId($memberId);
                    }

                } else {
                    return JSendResponse::createFail('Ainult super admin saab pärida teiste oste teistes konventides', 403);
                }
            }

        }

        if (!empty($memberName)) {
            $purchaseQuery->filterByMemberId(
                MemberQuery::create()
                    ->filterByFullName($memberName.'%', \Criteria::LIKE)
                    ->select(['id'])
                    ->find()
                    ->getData()
            );
        }

        if (!empty($createdByName)) {
            $purchaseQuery->filterByCreatedBy(
                MemberQuery::create()
                    ->filterByFullName($createdByName.'%', \Criteria::LIKE)
                    ->select(['id'])
                    ->find()
                    ->getData()
            );
        }

        if (!empty($dateFrom)) {
            try {
                $from = new DateTime($dateFrom);
                $purchaseQuery->filterByCreatedAt(['min' => $from]);
            } catch (\Exception $e) {
                return JSendResponse::createFail('Vigane alguskuupäev', 400, ['dateFrom' => $e->getMessage()]);
            }
        }

        if (!empty($dateUntil)) {
            try {
                $until = new DateTime($dateUntil);
                //Makes sure that the end date is included in the filter.
                $until->modify('+1 day');
                $purchaseQuery->filterByCreatedAt(['max' => $until]);
            } catch (\Exception $e) {
                return JSendResponse::createFail('Vigane lõppkuupäev', 400, ['dateUntil' => $e->getMessage()]);
            }
        }

        $purchaseQuery->orderByCreatedAt(\Criteria::DESC);

        $contentRange = $this->limitQuery($purchaseQuery, $limit, $offset);

        $purchases = $purchaseQuery->find();

        $resultPurchases = [];

        /** @var Transaction $purchase */
        foreach ($purchases as $purchase) {
            $resultPurchases[] = $purchase->getAjaxData();
        }

        return JSendResponse::createSuccess(
            ['purchases' => $resultPurchases],
            ['Content-Range' => "purchases ".$contentRange]
        );

    }
}
