<?php

namespace App\Repository;

use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @param $field
     * @param $value
     * @return User[] Returns an array of User objects
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findByExampleField($field, $value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.'.$field.' = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.email = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function updateUser($user): ?User
    {
        $qb = $this->createQueryBuilder('u');
        $q = $qb->update()
            ->set('u.request', '?1')
            ->set('u.open', '?2')
            ->set('u.token', '?3')
            ->set('u.password', '?4')
            ->where('u.id = ?5')
            ->andWhere('u.open = 1')
            ->setParameter(1, new DateTime())
            ->setParameter(2, 1)
            ->setParameter(3, $user->getToken())
            ->setParameter(4, $user->getPassword())
            ->setParameter(5, $user->getId())
            ->getQuery();
        $p = $q->execute();

        return $user;
    }

}
