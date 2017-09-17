<?php

namespace Rotalia\APIBundle\Controller;


use Rotalia\InventoryBundle\Component\HttpFoundation\JSendResponse;
use Rotalia\UserBundle\Model\Status;
use Rotalia\UserBundle\Model\StatusCreditLimit;
use Rotalia\UserBundle\Model\StatusQuery;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class StatusCreditLimitsController extends DefaultController
{
    /**
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *          200 = "Returned when successful",
     *          403 = "Returned when user is not authenticated",
     *     },
     *     description="Fetch credit limits for all statuses",
     *     section="CreditLimit",
     * )
     * @return JSendResponse
     */
    public function listAction()
    {
        $this->requireUser();

        /** @var Status[] $statuses */
        $statuses = StatusQuery::create()
           ->leftJoinStatusCreditLimit('statusCreditLimit')
           ->with('statusCreditLimit')
           ->find();

        $data = [];
        foreach ($statuses as $status) {
            /** @var StatusCreditLimit $creditLimit */
            $creditLimit = $status->getStatusCreditLimits()->getFirst();
            if ($creditLimit == null) {
                $creditLimit = new StatusCreditLimit();
                $creditLimit->setStatus($status);
            }
            $data[] = $creditLimit->getAjaxData();
        }

        return JSendResponse::createSuccess(['creditLimits' => $data]);
    }

    /**
     * @ApiDoc(
     *     resource = true,
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         403 = "Returned when user is not super admin",
     *     },
     *     filters = {
     *          {"name"="creditLimit","type"="int","description"="New credit limit for the status. Should be 0 or negative. Positive values are converted to negative."},
     *     },
     *     description="Set credit limit for given status",
     *     section="CreditLimit",
     * )
     * @param Request $request
     * @param         $statusId
     *
     * @return JSendResponse
     */
    public function patchAction(Request $request, $statusId)
    {
        $this->requireSuperAdmin();

        $status = StatusQuery::create()->findPk($statusId);

        if (!$status) {
            return JSendResponse::createFail('Staatust ei leitud', 404);
        }

        $statusCreditLimit = $status->getStatusCreditLimits()->getFirst();
        if (!$statusCreditLimit) {
            $statusCreditLimit = new StatusCreditLimit();
            $statusCreditLimit->setStatus($status);
        }

        // Force negative value for credit limit
        $creditLimit = -1 * abs((int)$request->get('creditLimit'));
        $statusCreditLimit->setCreditLimit($creditLimit);
        $statusCreditLimit->save();

        return JSendResponse::createSuccess($statusCreditLimit->getAjaxData());
    }
}
