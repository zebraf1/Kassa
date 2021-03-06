<?php

namespace Rotalia\APIBundle\Command;

use Exception;
use Rotalia\APIBundle\Model\CreditNetting;
use Rotalia\APIBundle\Model\CreditNettingPeer;
use Rotalia\APIBundle\Model\CreditNettingRow;
use Rotalia\UserBundle\Model\Convent;
use Rotalia\UserBundle\Model\ConventQuery;
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
            // Convents where kassa is active.
            $activeConventIds = ConventQuery::create()
                ->filterByIsActive(true)
                ->select(['id'])
                ->find($connection)
                ->getData()
            ;

            $output->writeln(sprintf("Number of convents with active kassa: %d", count($activeConventIds)));

            // Members who are at active convents
            $memberIdsAtActiveConvents = MemberQuery::create()
                ->filterByKoondisedId($activeConventIds)
                ->select(['id'])
                ->find($connection)
                ->getData()
            ;

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

            $output->writeln(sprintf("Number of members with non-zero credit: %d", count($memberCreditsByMemberId)));

            // Incoming credit for each convent
            /** @var MemberCredit[] $memberCredits */
            $memberCredits = MemberCreditQuery::create()
                ->joinMember()
                ->useMemberQuery()
                ->filterByKoondisedId($activeConventIds)
                ->endUse()
                ->where('ollekassa_member_credit.convent_id <> Member.koondised_id')
                ->groupBy('Member.koondised_id')
                ->withColumn('SUM(credit)')
                ->addAsColumn('Member.koondised_id', 'liikmed.koondised_id')
                ->find($connection)
            ;

            $output->writeln("Incoming credits:");
            $memberCreditsByConventIdIn = [];
            foreach ($memberCredits as $memberCredit) {
                $memberCreditsByConventIdIn[$memberCredit->getVirtualColumn('Member.koondised_id')] = $memberCredit->getVirtualColumn('SUMcredit');
                $output->writeln(sprintf("%s: %.2f",
                    ConventQuery::create()->findPk($memberCredit->getVirtualColumn('Member.koondised_id'), $connection)->getName(),
                    $memberCredit->getVirtualColumn('SUMcredit')
                    )
                );
            }

            // Outgoing credit for each convent
            $memberCredits = MemberCreditQuery::create()
                ->joinMember()
                ->useMemberQuery()
                ->filterByKoondisedId($activeConventIds)
                ->endUse()
                ->groupByConventId()
                ->where('ollekassa_member_credit.convent_id <> Member.koondised_id')
                ->withColumn('SUM(credit)')
                ->find($connection)
            ;

            $output->writeln("Outgoing credits:");
            $memberCreditsByConventIdOut = [];
            foreach ($memberCredits as $memberCredit) {
                $memberCreditsByConventIdOut[$memberCredit->getConventID()] = $memberCredit->getVirtualColumn('SUMcredit');
                $output->writeln(sprintf("%s: %.2f", $memberCredit->getConvent()->getName(), $memberCredit->getVirtualColumn('SUMcredit')));

            }

            // Redistribute the credits
            MemberCreditQuery::create()
                ->filterByMemberId($memberIdsAtActiveConvents)
                ->delete($connection)
            ;

            $output->writeln("Members whose credit is not moved:");
            foreach ($memberCreditsByMemberId as $memberId => $credit) {
                $member = MemberQuery::create()->findPk($memberId);
                if (in_array($member->getConventId(), $activeConventIds)) {
                    $memberCredit = new MemberCredit();
                    $memberCredit->setMemberId($memberId);
                    $memberCredit->setConventId($member->getKoondisedId());
                    $memberCredit->setCredit($credit);
                    $memberCredit->save($connection);
                } else {
                    $output->writeln($member->getFullName());
                }
            }
            $output->writeln("Credit redistributed!");

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

            $output->writeln("Credit netting inserted!");

            $connection->commit();
            $output->writeln("Success!");

        } catch (Exception $e) {
            $output->writeln("Caught an exeption:");
            $output->writeln($e->getMessage());
            $output->writeln("Rooling back!");
            $connection->rollback();
            throw $e;
        }
        $output->writeln("All done!");
    }
}
