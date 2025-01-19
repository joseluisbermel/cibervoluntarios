<?php

namespace App\Repository;

use App\Entity\Motorbike;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Motorbike>
 *
 * @method Customer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customer[]    findAll()
 * @method Customer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotorbikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Motorbike::class);
    }

    /**
     * Save Motorbike
     * @param Motorbike $motorbike
     * @return void
     */
    public function save(Motorbike $motorbike): void
    {
        $this->_em->persist($motorbike);
        $this->_em->flush();
    }

    /**
     * List limited edition
     * @return array
     */
    public function findLimitedEdition(): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.limitedEdition = :limited')
            ->setParameter('limited', true)
            ->orderBy('m.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
