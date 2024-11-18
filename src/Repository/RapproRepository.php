<?php

namespace App\Repository;

use App\Entity\Rappro;
use App\Utilities\Constant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Content;

/**
 * @extends ServiceEntityRepository<Rappro>
 *
 * @method Rappro|null find($id, $lockMode = null, $lockVersion = null)
 * @method Rappro|null findOneBy(array $criteria, array $orderBy = null)
 * @method Rappro[]    findAll()
 * @method Rappro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RapproRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Rappro::class);
    }

    /**
     * @param Rappro $entity
     * @param bool $flush
     * @return void
     */
    public function save(Rappro $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Rappro $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Rappro $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
