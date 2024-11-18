<?php

namespace App\Repository;

use App\Entity\OrStock;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Utilities\Util;
use stdClass;

/**
 * @extends ServiceEntityRepository<OrStock>
 *
 * @method OrStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrStock[]    findAll()
 * @method OrStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrStockRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrStock::class);
    }

    /**
     * @return array
     */
    public function findByIsLast(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isLast = :val')
            ->setParameter('val', 1)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function findByIsVendu(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.estVendu = :val')
            ->setParameter('val', 1)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array
     */
    public function findByIsPreOrder(): array
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.isPreOrder = :val1')
            ->setParameter('val1', 1)
            ->andWhere('o.isLast = :val2')
            ->setParameter('val2', 1)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param OrStock $entity
     * @param bool $flush
     * @return void
     */
    public function save(OrStock $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return object
     */
    public function getStock(): object
    {
        $res = new stdClass();
        $initial = 0;
        $preOrder = 0;
        ($this->findByIsLast() != null || count($this->findByIsLast()) != 0) ? $initial = count($this->findByIsLast()) : $initial;
        ($this->findByIsPreOrder() != null || count($this->findByIsPreOrder()) != 0) ? $preOrder = count($this->findByIsPreOrder()) : $preOrder;
        $actuel = $initial - $preOrder;
        $res->initial = $initial;
        $res->actuel = $actuel;
        return $res;
    }

    /**
     * @param $ref
     * @return OrStock
     */
    public function findByRef($ref): OrStock
    {
        $orStock = new OrStock();
        $condition = [
            'ref' => $ref,
            'isLast' => 1
        ];
        $ref = $this->findOneBy($condition);
        $ref !== null ? $orStock = $ref : $orStock;
        return $orStock;
    }

    /**
     * @return OrStock|null
     */
    public function findByIsNotPreOrderAndLast(): OrStock|null
    {
        $condition = [
            'isPreOrder' => 0,
            'isLast' => 1
        ];
        return $this->findOneBy($condition, ['id' => 'ASC']);
    }

    /**
     * @param $data
     * @return int
     */
    public function saveMultipleParExcel($data): int
    {
        $res = 0;
        if (count($data) > 0) {
            $id = 0;
            foreach ($data as $value) {
                $or = new OrStock();
                $lastId = $this->findOneBy([], ['id' => 'DESC']);
                $lastId != null || $lastId != 0 ? $id = $lastId->getId() : $id;
                $or->setId(Util::getLastId($id));
                $or->setRef($value[0]);
                $this->findByRef($value[0])->getId() == null ? $this->save($or, true) : null;
            }
            $res = 1;
        }
        return $res;
    }
}
