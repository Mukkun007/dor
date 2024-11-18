<?php

namespace App\Repository;

use App\Entity\UserBack;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<UserBack>
 *
 * @method UserBack|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBack|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBack[]    findAll()
 * @method UserBack[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBackRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserBack::class);
    }

    /**
     * @param UserBack $entity
     * @param bool $flush
     * @return void
     */
    public function save(UserBack $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param UserBack $entity
     * @param bool $flush
     * @return void
     */
    public function remove(UserBack $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof UserBack) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }
}
