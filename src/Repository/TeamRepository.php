<?php

namespace App\Repository;

use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    private $venueRepository;

    public function __construct(ManagerRegistry $registry, VenueRepository $venueRepository)
    {
        $this->venueRepository = $venueRepository;
        parent::__construct($registry, Team::class);
    }

    public function createOrUpdateTeam(array $team): Team {
        $entity = $this->findOneBy(['espn_id' => $team['espn_id']]);

        if ($team['abbreviation'] === 'WSH') {
            $team['displayName'] = 'Washington Football Team';
            $team['name'] = 'Football Team';
        }

        if (!$entity) {
            $entity = new Team;
            $entity->setEspnId($team['espn_id']);
        }

        if (!empty($team['espn_venue_id'])) {
            $venue = $this->venueRepository->findOneBy(['espn_id' => $team['espn_venue_id']]);
            if ($venue) {
                $entity->setVenue($venue);
            }
        }

        $entity->setLocation($team['location']);
        $entity->setName($team['name']);
        $entity->setAbbreviation($team['abbreviation']);
        $entity->setDisplayName($team['displayName']);

        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return $entity;
    }

    // /**
    //  * @return Team[] Returns an array of Team objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Team
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
