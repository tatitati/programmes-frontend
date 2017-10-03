<?php
declare(strict_types = 1);

namespace App\Fixture\Doctrine\EntityRepository;

use App\Fixture\Doctrine\Entity\Scenario;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ScenarioRepository extends EntityRepository
{
    public function findByName(string $name): ?Scenario
    {
        $qb = $this->createQueryBuilder('scenario')
            ->andWhere('scenario.name = :name')
            ->setParameter('name', $name);

        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_OBJECT);
    }

    public function saveScenarioWithFixtures(Scenario $scenario, array $httpFixtures): void
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            $em->persist($scenario);
            $em->flush();
            foreach ($httpFixtures as $httpFixture) {
                $em->persist($httpFixture);
            }
            $em->flush();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }
        $em->getConnection()->commit();
    }

    public function deleteScenarioAndFixtures(Scenario $scenario): void
    {
        $em = $this->getEntityManager();
        $em->getConnection()->beginTransaction();
        try {
            // Just delete the scenario. The cascade will take care of the fixtures
            $em->remove($scenario);
            $em->flush();
        } catch (\Exception $e) {
            $em->getConnection()->rollBack();
            throw $e;
        }
        $em->getConnection()->commit();
    }
}
