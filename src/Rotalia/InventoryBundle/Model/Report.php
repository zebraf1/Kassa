<?php

namespace Rotalia\InventoryBundle\Model;

use DateTime;
use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseReport;
use Rotalia\UserBundle\Model\Member;

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
     * @inheritdoc
     */
    public function setMember(Member $v = null)
    {
        $this->setName($v->getFullName());
        parent::setMember($v);
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
        if (empty($previousVerification)) {
            $this->expectedProfit = 0;
            $this->actualProfit = 0;
            return;
        }

        $productAmounts = [];
        $updatesBetween = ReportQuery::findUpdateReportsBetween($previousVerification, $this);
        $initialCash = doubleval($previousVerification->getCash() * 100);

        //Previous report amounts
        foreach ($previousVerification->getReportRows() as $reportRow) {
            $productAmounts[$reportRow->getProductId()]['start'] = $reportRow->getAmount();
        }

        //Add update values to initial value
        foreach ($updatesBetween as $report) {
            $initialCash += doubleval($report->getCash() * 100);

            foreach ($report->getReportRows() as $reportRow) {
                if (isset($productAmounts[$reportRow->getProductId()]['start'])) {
                    $productAmounts[$reportRow->getProductId()]['start'] += $reportRow->getAmount();
                } else {
                    $productAmounts[$reportRow->getProductId()]['start'] = $reportRow->getAmount();
                }
            }
        }

        $currentCash = doubleval($this->getCash() * 100);

        //Current report amounts
        foreach ($this->getReportRows() as $reportRow) {
            $productAmounts[$reportRow->getProductId()]['end'] = $reportRow->getAmount();
        }

        //Calculate expected profit
        $expectedProfit = 0;
        foreach ($productAmounts as $productId => $productAmount) {
            $product = ProductQuery::create()->findPk($productId);
            $start = !empty($productAmount['start']) ? $productAmount['start'] : 0;
            $end = !empty($productAmount['end']) ? $productAmount['end'] : 0;
            $diff = ($start - $end) * $product->getPrice() * 100;
            $expectedProfit += $diff;
        }

        //We expect that cash must increase the same amount as product value has decreased
        $this->expectedProfit = $expectedProfit / 100;
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
