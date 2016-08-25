<?php

namespace Rotalia\InventoryBundle\Model;

use DateTime;
use Rotalia\InventoryBundle\Classes\XClassifier;
use Rotalia\InventoryBundle\Model\om\BaseReport;
use Rotalia\UserBundle\Model\GuardDuty;
use Rotalia\UserBundle\Model\GuardDutyQuery;
use Rotalia\UserBundle\Model\Member;

class Report extends BaseReport
{
    const TYPE_VERIFICATION = 'VERIFICATION'; //Physical verification report
    const TYPE_UPDATE = 'UPDATE'; //Inventory update report

    public static $types = [
        self::TYPE_VERIFICATION,
        self::TYPE_UPDATE,
    ];

    protected $expectedProfit = null;
    protected $actualProfit = null;
    protected $initialCash = null;
    protected $totalValue = null;
    /** @var null|Report */
    protected $previousVerification = null;
    protected $productAmounts = [];
    protected $currentAmounts = [];
    protected $expectedAmounts = null;

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
            ->filterByStatus(XClassifier::STATUS_ACTIVE)
            ->orderBySeq()
            ->find()
        ;

        $criteria = ReportRowQuery::create()
            ->useProductQuery()
            ->filterByStatus(XClassifier::STATUS_ACTIVE)
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
     * Get ReportRow object for form
     *
     * @return array
     */
    public function getReportRowsFormEdit()
    {
        $reportRows = $this->getReportRows();

        $reportRowsByProductId = [];

        foreach ($reportRows as $reportRow) {
            $reportRowsByProductId[$reportRow->getProductId()] = $reportRow;
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
        $this->previousVerification = $previousVerification;

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
            $this->productAmounts[$productId] = $start;
        }

        //We expect that cash must increase the same amount as product value has decreased
        $this->expectedProfit = $expectedProfit / 100;
        $this->actualProfit = ($currentCash - $initialCash) / 100;
        $this->initialCash = $initialCash / 100;
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
     * @return null
     * @throws \PropelException
     */
    public function getExpectedCash()
    {
        if ($this->isUpdate()) {
            return null;
        }

        $this->calculateProfit();

        // Find cash transactions between this and previous verification reports
        $transactionsQuery = TransactionQuery::create()
            ->filterByType([Transaction::TYPE_CASH_PURCHASE, Transaction::TYPE_CASH_PAYMENT])
        ;

        if ($this->previousVerification !== null) {
            $transactionsQuery->filterByCreatedAt($this->previousVerification->getCreatedAt(), \Criteria::GREATER_EQUAL);
        }

        // Get all recent transactions if new
        if (!$this->isNew()) {
            $transactionsQuery->filterByCreatedAt($this->getCreatedAt(), \Criteria::LESS_THAN);
        }

        $transactionsQuery->withColumn('SUM(ollekassa_transaction.sum)', 'income');

        $result = $transactionsQuery->findOne();
        $income = doubleval($result->getVirtualColumn('income'));

        return $this->initialCash + $income;
    }

    /**
     * @param $productId
     * @return int|mixed
     * @throws \PropelException
     */
    public function getExpectedProductAmount($productId)
    {
        // Local caching
        if ($this->expectedAmounts !== null) {
            return isset($this->expectedAmounts[$productId]) ? $this->expectedAmounts[$productId] : 0;
        }

        $this->calculateProfit();

        // Find cash transactions between this and previous verification reports
        $transactionsQuery = TransactionQuery::create()
            ->filterByProductId(null, \Criteria::ISNOTNULL)
        ;

        if ($this->previousVerification !== null) {
            $transactionsQuery->filterByCreatedAt($this->previousVerification->getCreatedAt(), \Criteria::GREATER_EQUAL);
        }

        // Get all recent transactions if new
        if (!$this->isNew()) {
            $transactionsQuery->filterByCreatedAt($this->getCreatedAt(), \Criteria::LESS_THAN);
        }

        $transactionsQuery
            ->groupByProductId()
            ->withColumn('SUM(ollekassa_transaction.amount)', 'consumed')
        ;

        /** @var Transaction[] $result */
        $result = $transactionsQuery->find();

        $this->expectedAmounts = [];

        // Set initial amounts to expected amounts for products that haven't been purchased
        foreach ($this->productAmounts as $id => $amount) {
            $this->expectedAmounts[$id] = $amount;
        }

        // Update amounts for products that were purchased since previous report
        foreach ($result as $tx) {
            $consumed = doubleval($tx->getVirtualColumn('consumed'));

            // Reduce by consumed amount
            if (isset($this->expectedAmounts[$tx->getProductId()])) {
                $this->expectedAmounts[$tx->getProductId()] -= $consumed;
            } else {
                $this->expectedAmounts[$tx->getProductId()] = $consumed;
            }
        }

        return isset($this->expectedAmounts[$productId]) ? $this->expectedAmounts[$productId] : 0;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt($format = null)
    {
        // Pass current time for new report
        if ($this->isNew() && $format === null) {
            return new DateTime();
        }

        return parent::getCreatedAt($format);
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

    /**
     * Returns the members who should be on guard duty for the same date
     *
     * @return Member[]
     * @throws \PropelException
     */
    public function getGuardDutyMembers()
    {
        /** @var GuardDuty[] $guardDuties */
        $guardDuties = GuardDutyQuery::create()
            ->filterByDate($this->getCreatedAt('Y-m-d'))
            ->useGuardDutyCycleQuery()
                ->filterByConventId($this->getConventId())
            ->endUse()
            ->find()
        ;

        $result = [];

        foreach ($guardDuties as $guardDuty) {
            // Sometimes member ID is 0, avoid error
            if (!$guardDuty->getMemberId()) {
                continue;
            }

            //Avoid duplicate members when in 2 duty cycles at the same time
            $result[$guardDuty->getMemberId()] = $guardDuty->getMember();
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getAjaxData()
    {
        $reportRows = [];

        foreach ($this->getReportRows() as $reportRow) {
            $reportRows[] = $reportRow->getAjaxData();
        }

        return [
            'id' => $this->getId(),
            'memberName' => $this->getMemberName(),
            'guardDutyMembers' => $this->getGuardDutyMembers(),
            'memberId' => $this->getMemberId(),
            'type' => $this->getType(),
            'createdAt' => $this->getCreatedAt('Y-m-d H:i:s'),
            'cash' => $this->getCash(),
            'expectedCash' => $this->getExpectedCash(),
            'profit' => $this->getActualProfit(),
            'expectedProfit' => $this->getExpectedProfit(),
            'reportRows' => $reportRows,
        ];
    }
}
