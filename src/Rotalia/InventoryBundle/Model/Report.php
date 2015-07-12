<?php

namespace Rotalia\InventoryBundle\Model;

use DateTime;
use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseReport;

class Report extends BaseReport
{
    const TYPE_VERIFICATION = 'VERIFICATION'; //Physical verification report
    const TYPE_UPDATE = 'UPDATE'; //Inventory update report

    protected $expectedProfit = null;
    protected $actualProfit = null;
    protected $totalValue = null;

    private $calculatedTotalProductValue = null;

    /**
     * @return float
     */
    public function getCash()
    {
        return doubleval($this->cash);
    }

    /**
     * Get ReportRow object for form
     *
     * @return array
     */
    public function getReportRowsForm()
    {
        /** @var Product[] $activeProducts */
        $activeProducts = ProductQuery::create()
            ->filterByStatusId(XClassifier::STATUS_ACTIVE)
            ->orderBySeq()
            ->find()
        ;

        $criteria = ReportRowQuery::create()
            ->useProductQuery()
            ->filterByStatusId(XClassifier::STATUS_ACTIVE)
            ->endUse()
        ;
        $reportRows = $this->getReportRows($criteria);

        $reportRowsByProductId = [];

        foreach ($reportRows as $reportRow) {
            $reportRowsByProductId[$reportRow->getProductId()] = $reportRow;
        }

        foreach ($activeProducts as $product) {
            if (!isset($reportRowsByProductId[$product->getId()])) {
                $reportRow = new ReportRow();
                $reportRow
                    ->setProduct($product)
                    ->setReport($this);
                $reportRowsByProductId[$product->getId()] = $reportRow;
            }
        }

        return $reportRowsByProductId;
    }

    /**
     * Setter for form
     *
     * @param $v
     */
    public function setReportRowsForm($v)
    {
        if (is_array($v)) {
            $coll = new \PropelArrayCollection();
            $coll->setData($v);
            $this->setReportRows($coll);
        } else {
            $this->setReportRows($v);
        }
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAtLocalized($format, $locale = 'et_EE')
    {
        /** @var DateTime $createdAt */
        $createdAt = parent::getCreatedAt();
        setlocale(LC_TIME, $locale);
        return strftime($format, $createdAt->getTimestamp());
    }

    /**
     * Whether the report is an inventory update report
     *
     * @return bool
     */
    public function isUpdate()
    {
        return $this->getType() == self::TYPE_UPDATE;
    }

    /**
     * @return string
     */
    public function getMemberName()
    {
        if ($member = $this->getMember()) {
            return $member->getFullName();
        }

        return $this->getName();
    }


    /**
     * Get report row amount for given product
     *
     * @param Product $product
     * @return ReportRow|null
     */
    public function getReportRowForProduct(Product $product)
    {
        foreach ($this->getReportRows() as $reportRow) {
            if ($reportRow->getProductId() == $product->getId()) {
                return $reportRow;
            }
        }

        return null;
    }

    /**
     * Ensure ReportRow current price matches the products current price.
     */
    public function updateRowPrices()
    {
        foreach ($this->getReportRows() as $reportRow) {
            $reportRow->updateCurrentPrice();
        }
    }


    //Profit calculation helpers

    /**
     * Calculate expected total value and actual total value
     */
    public function calculateProfit()
    {
        if ($this->expectedProfit !== null) {
            return;
        }

        $previousVerification = ReportQuery::findPreviousVerificationReport($this);
        if ($previousVerification) {
            $updatesBetween = ReportQuery::findUpdateReportsBetween($previousVerification, $this);
            $initialProductValue = doubleval($previousVerification->getTotalProductValue() * 100);
            $initialCash = doubleval($previousVerification->getCash() * 100);

            //Add update values to initial value
            foreach ($updatesBetween as $report) {
                $initialProductValue += doubleval($report->getTotalProductValue() * 100);
                $initialCash += doubleval($report->getCash() * 100);
            }

        } else {
            $initialProductValue = 0;
            $initialCash = 0;
        }


        $currentProductValue = doubleval($this->getTotalProductValue() * 100);
        $currentCash = doubleval($this->getCash() * 100);

        //We expect that cash must increase the same amount as product value has decreased
        $this->expectedProfit = ($initialProductValue - $currentProductValue) / 100;
        $this->actualProfit = ($currentCash - $initialCash) / 100;
    }

    /**
     * Calculate cash and product total value
     *
     * @return float
     */
    public function getTotalReportValue()
    {
        if ($this->totalValue !== null) {
            return $this->totalValue;
        }

        //Note: convert to integer cents to avoid PHP double sum issue
        $cashValue = doubleval($this->getCash() * 100);

        $productValue = $this->getTotalProductValue() * 100;

        $this->totalValue = ($cashValue + $productValue) / 100;

        return $this->totalValue;
    }

    /**
     * Total value of all products sum(price * amount)
     *
     * @return float
     */
    public function getTotalProductValue()
    {
        if ($this->calculatedTotalProductValue !== null) {
            return $this->calculatedTotalProductValue;
        }
        $totalValue = 0;

        foreach ($this->getReportRows() as $reportRow) {
            //Note: convert to integer cents to avoid PHP double sum issue
            $price = doubleval($reportRow->getCurrentPrice() * 100);
            $amount = doubleval($reportRow->getAmount());
            $totalValue += ($price * $amount);
        }

        $this->calculatedTotalProductValue = $totalValue / 100;

        return $this->calculatedTotalProductValue;
    }

    /**
     * Get expected profit for the current report
     *
     * @return null
     */
    public function getExpectedProfit()
    {
        $this->calculateProfit();

        return $this->expectedProfit;
    }

    /**
     * Get actual profit for the current report
     *
     * @return null
     */
    public function getActualProfit()
    {
        $this->calculateProfit();

        return $this->actualProfit;
    }

    /**
     * Returns true when report profit is less than expected profit
     *
     * @return bool
     */
    public function isNegativeProfit()
    {
        //You cannot compare floating point numbers normally
        if ($this->actualProfit - $this->expectedProfit < -0.0001) {
            return true;
        }

        return false;
    }
}
