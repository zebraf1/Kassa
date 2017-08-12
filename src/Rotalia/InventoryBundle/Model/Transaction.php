<?php

namespace Rotalia\InventoryBundle\Model;

use Rotalia\InventoryBundle\Model\om\BaseTransaction;

class Transaction extends BaseTransaction
{
    const TYPE_CASH_PURCHASE = 'CASH_PURCHASE';
    const TYPE_CREDIT_PURCHASE = 'CREDIT_PURCHASE';

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
            default:
                return 'Tundmatu tüüp';
                break;
        }
    }

    /**
     * Returns transaction total sum
     * @return double
     */
    public function calculateSum()
    {
        $sum = doubleval($this->getAmount()) * doubleval($this->getCurrentPrice());
        $sum = round($sum, 2);

        return $sum;
    }
}
