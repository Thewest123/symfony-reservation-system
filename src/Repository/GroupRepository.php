<?php

namespace App\Repository;

use App\Entity\Group;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Group>
 *
 * @method Group|null find($id, $lockMode = null, $lockVersion = null)
 * @method Group|null findOneBy(array $criteria, array $orderBy = null)
 * @method Group[]    findAll()
 * @method Group[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GroupRepository extends ServiceEntityRepository
{
    use SaveRemoveTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Group::class);

        $this->setEntityRepository($this);
    }

    /**
     * @return Group[] Returns an array of Group objects
     */
    public function findRecursive(int $id)
    {
        $group = $this->find($id);
        $groups = [$group];
        return $this->getSubgroups($groups);
    }

    /**
     * @return Group[] Returns an array of Group objects
     */
    private function getSubgroups($groups)
    {
        // clone the array
        $newGroups = array_merge($groups);

        // loop over each group in array
        foreach ($groups as $key => $group) {
            // loop over each subgroup
            $subgroups = $group->getSubgroups();
            foreach ($subgroups as $key2 => $subgroup) {
                // if subgroup is not in array, get all subgroups
                if (!in_array($subgroup, $newGroups)) {
                    array_push($newGroups, $subgroup);
                    $newGroups = $this->getSubgroups($newGroups);
                }
            }
        }

        return $newGroups;
    }

    public function findByName($name)
    {
        return $this->createQueryBuilder('u')
            ->where('u.name = :name')
            ->setParameter('name', $name)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return Group[] Returns an array of Group objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Group
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
