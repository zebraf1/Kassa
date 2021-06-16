<?php

namespace Rotalia\APIBundle\Model;

use PropelPDO;
use Rotalia\APIBundle\Model\om\BaseTransaction;

class Transaction extends BaseTransaction
{
    const TYPE_CASH_PURCHASE = 'CASH_PURCHASE';
    const TYPE_CREDIT_PURCHASE = 'CREDIT_PURCHASE';
    const TYPE_CASH_PAYMENT = 'CASH_PAYMENT';
    const TYPE_CREDIT_PAYMENT = 'CREDIT_PAYMENT';

    public function getProduct(PropelPDO $con = null, $doQuery = true) {
        $product = parent::getProduct($con, $doQuery);
        $product->setConventId($this->getConventId());
        return $product;
    }

    /**
     * @return string
     */
    public function getTypeText()
    {
        switch ($this->getType()) {
            case self::TYPE_CASH_PURCHASE:
                return 'Sularahamakse';
                break;
            case self::TYPE_CREDIT_PURCHASE:
                return 'Krediidimakse';
                break;
            case self::TYPE_CASH_PAYMENT:
                return 'Tagasimakse kassasse';
                break;
            case self::TYPE_CREDIT_PAYMENT:
                return 'Tagasimakse krediit';
                break;
            default:
                return 'Tundmatu tüüp';
                break;
        }
    }

    /**
     * @return bool
     */
    public function isPayment()
    {
        $type = $this->getType();
        return in_array($type, [self::TYPE_CREDIT_PAYMENT, self::TYPE_CASH_PAYMENT]);
    }

    /**
     * Returns transaction total sum
     * @return double
     */
    public function calculateSum()
    {
        $sum = doubleval($this->getCount()) * doubleval($this->getCurrentPrice());
        $sum = round($sum, 2);
        $this->setSum($sum);

        return $sum;
    }

    /**
     * @return array
     */
    public function getAjaxData()
    {
        return [
            'id' => $this->getId(),
            'member' => $this->getMemberRelatedByMemberId() ? $this->getMemberRelatedByMemberId()->getAjaxName() : null,
            'createdBy' => $this->getMemberRelatedByCreatedBy() ? $this->getMemberRelatedByCreatedBy()->getAjaxName() : null,
            'count' => intval($this->getCount()),
            'price' => doubleval($this->getCurrentPrice()),
            'product' => $this->getProduct() ? $this->getProduct()->getAjaxData() : null,
            'convent' => $this->getConvent() ? $this->getConvent()->getName() : null,
            'createdAt' => $this->getCreatedAt()->format('H:i d.m.Y')
        ];
    }
}
