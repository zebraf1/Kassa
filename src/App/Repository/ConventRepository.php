<?php

namespace App\Repository;

use App\Entity\Convent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Convent>
 * @method Convent|null findOneBy(array $criteria, array|null $orderBy = null)
 */
class ConventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Convent::class);
    }

    /**
     * @return Convent[]
     */
    public function getActiveConvents(): array
    {
        return $this->findBy(['isActive' => 1], ['id' => 'ASC']);
    }
}
