<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseTransaction;

class Transaction extends BaseTransaction
{
    const TYPE_CASH_PURCHASE = 'CASH_PURCHASE';
    const TYPE_CREDIT_PURCHASE = 'CREDIT_PURCHASE';
    const TYPE_CASH_PAYMENT = 'CASH_PAYMENT';
    const TYPE_CREDIT_PAYMENT = 'CREDIT_PAYMENT';

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
}
