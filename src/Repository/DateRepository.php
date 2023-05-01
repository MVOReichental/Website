<?php
namespace App\Repository;

use App\Date;
use App\Entity\date\DateList;
use App\Entity\date\Entry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Entry::class);
    }

    public function get(bool $onlyPublic = true, int $limit = 1000): DateList
    {
        $queryBuilder = $this->createQueryBuilder("date");

        $where = [];

        if ($onlyPublic) {
            $where[] = $queryBuilder->expr()->eq("date.isPublic", true);
        }

        $isEndDateInFuture = $queryBuilder->expr()->andX($queryBuilder->expr()->isNotNull("date.endDate"), "date.endDate > :now");
        $where[] = $queryBuilder->expr()->orX("date.startDate >= :now", $isEndDateInFuture);

        $dates = $queryBuilder
            ->where(...$where)
            ->orderBy("date.startDate", "asc")
            ->setMaxResults($limit)
            ->setParameter(":now", (new Date)->toDatabase())
            ->getQuery()
            ->getResult();

        return new DateList($dates);
    }

    public function getBetween(Date $startDate, Date $endDate, bool $onlyPublic = true, int $limit = 1000): DateList
    {
        $queryBuilder = $this->createQueryBuilder("date");
        $where = [];

        if ($onlyPublic) {
            $where[] = "date.isPublic";
        }

        $where[] = $queryBuilder->expr()->between("date.startDate", ":startDate", ":endDate");

        $dates = $queryBuilder
            ->where($where)
            ->orderBy("date.startDate", "asc")
            ->setMaxResults($limit)
            ->setParameter(":startDate", $startDate->toDatabase())
            ->setParameter(":endDate", $endDate->toDatabase())
            ->getQuery()
            ->getResult();

        return new DateList($dates);
    }

    public function getAll(bool $onlyPublic = true, int $limit = 1000): DateList
    {
        $queryBuilder = $this->createQueryBuilder("date");

        if ($onlyPublic) {
            $queryBuilder->where("date.isPublic");
        }

        $dates = $queryBuilder
            ->orderBy("date.startDate", "asc")
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return new DateList($dates);
    }
}