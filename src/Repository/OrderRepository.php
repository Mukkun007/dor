<?php

namespace App\Repository;

use App\Entity\Order;
use App\Utilities\Constant;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;

/**
 * @extends ServiceEntityRepository<Order>
 *
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @param Order $entity
     * @param bool $flush
     * @return void
     */
    public function save(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Order $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Order $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOrderByAgeMoin30AndCelibataire(): array{
        $data = Array();
        $liste = $this->createQueryBuilder('o')
            ->join('o.user', 'a')
            ->andWhere('a.marital_status = :marital')
            ->andWhere('o.isDeleted = :val1')
            ->andWhere('o.flagStatus = :flagstatus')
            ->andWhere('o.orStock is not null')
            ->setParameter('val1', 0)
            ->setParameter('marital', Constant::USER_MARITAL_STATUS_SINGLE)
            ->setParameter('flagstatus', Constant::STATUS_QUEUE)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();

        if(count($liste) > 0){
            foreach($liste as $value){
                $order = new Order();
                if($value->getUser()->getAge() < Constant::FILTER_AGE){
                    $order = $value;
                    array_push($data, $order);
                }
            }
        }
        return $liste;
    }

//    public function findOrderByAgeMoin30AndCelibataire(): array{
//        $current = new \DateTime();
//        $year = $current->format('Y');
//        return $this->createQueryBuilder('o')
//                    ->join('o.user', 'a')
//                    ->andWhere('a.marital_status = :marital')
//                    ->andWhere(
//                        $this->getEntityManager()->getExpressionBuilder()->diff($year,'a.birthday'
//                        ) . ' < :age_limit'
//                    )
//                    ->andWhere('o.flagStatus = :flagstatus')
//                    ->andWhere('o.orStock is not null')
//                    ->setParameter('marital', Constant::USER_MARITAL_STATUS_SINGLE)
//                    ->setParameter('age_limit', Constant::FILTER_AGE)
//                    ->setParameter('flagstatus', Constant::STATUS_QUEUE)
//                    ->orderBy('o.id', 'ASC')
//                    ->getQuery()
//                    ->getResult();
//    }

    public function validOrderMoins30AndCelibataire($listeOrder): int{
        $result = 0;
//        $listeOrder = $this->findOrderByAgeMoin30AndCelibataire();
        if(count($listeOrder) > 0){
            foreach($listeOrder as $order){
                $order->setFlagStatus(Constant::STATUS_VALID);
                $order->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
                $this->save($order,true);
            }
            $result = 1;
        }
        return $result;
    }

    public function findByFlagStatus($status){
        return $this->createQueryBuilder('o')
            ->andWhere('o.flagStatus = :flagstatus')
            ->setParameter('flagstatus', $status)
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllOrder(){
        return $this->createQueryBuilder('o')
            ->orderBy('o.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    // public function findByFlagStatus($status){
    //     return $this->createQueryBuilder('o')
    //         ->andWhere('o.flagStatus = :flagstatus')
    //         ->setParameter('flagstatus', $status)
    //         ->orderBy('o.id', 'ASC')
    //         ->getQuery()
    //         ->getResult();
    // }
}
