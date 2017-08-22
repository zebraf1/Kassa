<?php

namespace Rotalia\APIBundle\Command;

use Exception;
use Rotalia\InventoryBundle\Model\CreditNetting;
use Rotalia\InventoryBundle\Model\CreditNettingPeer;
use Rotalia\InventoryBundle\Model\CreditNettingRow;
use Rotalia\UserBundle\Model\MemberCredit;
use Rotalia\UserBundle\Model\MemberCreditQuery;
use Rotalia\UserBundle\Model\MemberQuery;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreditNettingCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('app:credit-netting')
            ->setDescription('Runs credit netting')
            ->setHelp('Rebalances credits between convents and calculates nettings.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $connection = \Propel::getConnection(CreditNettingPeer::DATABASE_NAME, \Propel::CONNECTION_WRITE);
        $connection->beginTransaction();

        try {
            // Sum the credit for all members
            $memberCredits = MemberCreditQuery::create()
                ->groupByMemberId()
                ->withColumn('SUM(credit)')
                ->find($connection)
            ;

            $memberCreditsByMemberId = [];
            foreach ($memberCredits as $memberCredit) {
                $memberCreditsByMemberId[$memberCredit->getMemberId()] = $memberCredit->getVirtualColumn('SUMcredit');
            }

            // Incoming credit for each convent
            /** @var MemberCredit[] $memberCredits */
            $memberCredits = MemberCreditQuery::create()
                ->joinMember()
                ->useMemberQuery()
                ->groupByKoondisedId()
                ->endUse()
                ->where('ollekassa_member_credit.convent_id <> Member.koondised_id')
                ->withColumn('SUM(credit)')
                ->addAsColumn('liikmed.koondised_id', 'liikmed.koondised_id')
                ->find($connection)
            ;

            $memberCreditsByConventIdIn = [];
            foreach ($memberCredits as $memberCredit) {
                $memberCreditsByConventIdIn[$memberCredit->getVirtualColumn('liikmed.koondised_id')] = $memberCredit->getVirtualColumn('SUMcredit');
            }

            // Outgoing credit for each convent
            $memberCredits = MemberCreditQuery::create()
                ->joinMember()
                ->groupByConventId()
                ->where('ollekassa_member_credit.convent_id <> Member.koondised_id')
                ->withColumn('SUM(credit)')
                ->find($connection)
            ;

            $memberCreditsByConventIdOut = [];
            foreach ($memberCredits as $memberCredit) {
                $memberCreditsByConventIdOut[$memberCredit->getConventID()] = $memberCredit->getVirtualColumn('SUMcredit');
            }

            // Redistribute the credits
            MemberCreditQuery::create()
                ->deleteAll($connection)
            ;

            foreach ($memberCreditsByMemberId as $memberId => $credit) {
                $member = MemberQuery::create()->findPk($memberId);
                $memberCredit = new MemberCredit();
                $memberCredit->setMemberId($memberId);
                $memberCredit->setConventId($member->getKoondisedId());
                $memberCredit->setCredit($credit);
                $memberCredit->save($connection);
            }

            // Insert Credit netting
            $creditNetting = new CreditNetting();
            $creditNetting->save($connection);

            $conventIds = array_unique(array_merge(array_keys($memberCreditsByConventIdIn), array_keys($memberCreditsByConventIdOut)));

            foreach ($conventIds as $conventId) {
                $creditIn = array_key_exists($conventId, $memberCreditsByConventIdIn) ? $memberCreditsByConventIdIn[$conventId] : 0;
                $creditOut = array_key_exists($conventId, $memberCreditsByConventIdOut) ? $memberCreditsByConventIdOut[$conventId] : 0;

                $creditNettingRow = new CreditNettingRow();
                $creditNettingRow->setCreditNetting($creditNetting);
                $creditNettingRow->setConventId($conventId);
                $creditNettingRow->setSum($creditIn - $creditOut);
                if ($creditIn - $creditOut === 0.0) {
                    $creditNettingRow->setNettingDone(1);
                }
                $creditNettingRow->save($connection);
            }

            $connection->commit();

        } catch (Exception $e) {
            $connection->rollback();
            throw $e;
        }

    }
}
