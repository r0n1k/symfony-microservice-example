<?php
namespace App\Services\EntityLogger\Repository;

use App\Services\EntityLogger\Entity\EntityLog;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;

class EntityLogRepository
{
    private $em;
    /** @var ObjectRepository */
    private $repo;

    public function __construct(EntityManagerInterface $em)
    {
        $this->repo = $em->getRepository(EntityLog::class);
        $this->em = $em;
    }

    public function find(int $id): ?EntityLog
    {
        /** @var EntityLog $project */
        $project = $this->repo->find($id);
        return $project;
    }


    public function findAllForEntity(object $entity): ?array
    {
        $entityClass = get_class($entity);
        $entityId = (string)$entity->getId();
        return $this->findAllByClassAndId($entityClass, $entityId);
    }

    public function findAllByClassAndId(string $entityClass, $entityId): ?array
    {
        return $this->repo->findBy(['objectClass' => $entityClass, 'objectId' => $entityId]);
    }

    public function getLastVersionByEntity(object $entity): int
    {
        $entityClass = get_class($entity);
        $entityId = (string)$entity->getId();
        /** @var $lastLogForEntity EntityLog[] */
        $lastLogForEntity = $this->repo->findBy(['objectClass' => $entityClass, 'objectId' => $entityId], ['id' => 'DESC'], 1);
        return $lastLogForEntity ? (int)$lastLogForEntity[0]->getVersion() : 0;
    }

    public function add(EntityLog $log){
        $this->em->persist($log);
    }
}
