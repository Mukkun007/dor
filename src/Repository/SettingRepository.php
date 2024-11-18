<?php

namespace App\Repository;

use App\Entity\Setting;
use App\Utilities\Constant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use DateTimeImmutable;
use PhpOffice\PhpSpreadsheet\Writer\Ods\Content;

/**
 * @extends ServiceEntityRepository<Setting>
 *
 * @method Setting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Setting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Setting[]    findAll()
 * @method Setting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SettingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Setting::class);
    }

    /**
     * @param Setting $entity
     * @param bool $flush
     * @return void
     */
    public function save(Setting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param Setting $entity
     * @param bool $flush
     * @return void
     */
    public function remove(Setting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param $name
     * @param $valeur
     * @return Setting
     */
    public function findSettingByName($name, $valeur): Setting
    {
        $setting = new Setting();
        $res = $this->findOneBy(["name" => $name]);
        if ($res == null) {
            $LastId = $this->findOneBy([], ['id' => 'DESC']);
            $LastId != null ? ($id = $LastId->getId() + 1) : ($id = 1);
            $setting->setId($id);
            $setting->setName($name);
            if ($name == Constant::NAME_CHECKBOX_NATIONALITE) {
                $valeur == null ? $setting->setValue(Constant::NOCHECKED_NATIONALITE) : $setting->setValue(Constant::CHECKED_NATIONALITE);
            } else {
                $setting->setValue($valeur);
            }
            $this->save($setting, true);
        } else {
            if ($res->getValue() == $valeur) {
                $setting = $res;
            } else {
                if ($name == Constant::NAME_CHECKBOX_NATIONALITE) {
                    $valeur == null ? $res->setValue(Constant::NOCHECKED_NATIONALITE) : $res->setValue(Constant::CHECKED_NATIONALITE);
                } else {
                    $res->setValue($valeur);
                }
                $res->setUpdatedAt((new DateTimeImmutable())->format('Y-m-d'));
                $this->save($res, true);
                $setting = $res;
            }
        }
        return $setting;
    }
}
