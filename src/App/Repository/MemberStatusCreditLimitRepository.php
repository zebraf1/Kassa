<?php

namespace App\Repository;

use App\Entity\MemberStatusCreditLimit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MemberStatusCreditLimit>
 */
class MemberStatusCreditLimitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MemberStatusCreditLimit::class);
    }
}
