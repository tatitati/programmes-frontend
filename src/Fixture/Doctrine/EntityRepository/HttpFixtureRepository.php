<?php
declare(strict_types = 1);

namespace App\Fixture\Doctrine\EntityRepository;

use App\Fixture\Doctrine\Entity\HttpFixture;
use App\Fixture\Doctrine\Entity\Scenario;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class HttpFixtureRepository extends EntityRepository
{
    /**
     * @param Scenario $scenario
     * @return HttpFixture[]
     */
    public function findAllByScenario(Scenario $scenario): array
    {
        $qb = $this->createQueryBuilder('httpfixture')
            ->andWhere('httpfixture.scenario = :scenario')
            ->setParameter('scenario', $scenario);

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }
}
