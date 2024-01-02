<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

trait SaveRemoveTrait
{
    private EntityRepository $entityRepository;

    public function setEntityRepository(EntityRepository $serviceEntityRepository): void
    {
        $this->entityRepository = $serviceEntityRepository;
    }

    public function save(object $entity, bool $flush = true): void
    {
        $this->entityRepository->getEntityManager()->persist($entity);
        if ($flush) $this->entityRepository->getEntityManager()->flush();
    }

    public function remove(object $entity, bool $flush = true): void
    {
        $this->entityRepository->getEntityManager()->remove($entity);
        if ($flush) $this->entityRepository->getEntityManager()->flush();
    }
}
