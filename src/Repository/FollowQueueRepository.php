<?php

namespace App\Repository;

use App\Entity\FollowQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FollowQueue|null find($id, $lockMode = null, $lockVersion = null)
 * @method FollowQueue|null findOneBy(array $criteria, array $orderBy = null)
 * @method FollowQueue[]    findAll()
 * @method FollowQueue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FollowQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FollowQueue::class);
    }

    public function create(int $twitterId, array $hashtags)
    {
        $queue = new FollowQueue();
        $queue->setTwitterId($twitterId);
        $queue->setCriteria($hashtags);

        $this->getEntityManager()->persist($queue);
        $this->getEntityManager()->flush();
    }

    public function markFollowed(int $twitterId) {
        $queue = $this->findOneBy(['twitterId' => $twitterId]);
        $queue->setDateFollowed(new \DateTime());

        $this->getEntityManager()->persist($queue);
        $this->getEntityManager()->flush();
    }

    public function markUnfollowed(int $twitterId) {
        $queue = $this->findOneBy(['twitterId' => $twitterId]);

        $this->getEntityManager()->remove($queue);
        $this->getEntityManager()->flush();
    }

    public function markFollowedBack(FollowQueue $queue) {
        $queue->setFollowedBack(true);

        $this->getEntityManager()->persist($queue);
        $this->getEntityManager()->flush();
    }

    public function getAccountsToUnfollow() {
        $qb = $this->createQueryBuilder('f')
            ->where('f.DateFollowed < :min_date')
            ->setParameter('min_date', (new \DateTime('-3 days'))->format('Y-m-d 00:00:00'));

        $query = $qb->getQuery();

        return $query->execute();
    }

    // /**
    //  * @return FollowQueue[] Returns an array of FollowQueue objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FollowQueue
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
