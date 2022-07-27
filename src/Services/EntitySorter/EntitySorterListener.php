<?php

namespace App\Services\EntitySorter;

use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Services\EntityLogger\Annotation\Versioned;
use App\Services\EntityLogger\Entity\EntityLog;
use App\Services\EntityLogger\Repository\EntityLogRepository;
use App\Services\EntitySorter\Annotation\Sorted;
use App\Services\EntitySorter\Sorters\SorterFactory;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;

class EntitySorterListener implements EventSubscriber
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /** @var $uow UnitOfWork */
    private $uow;
    /** @var EntityManagerInterface */
    private $em;
    private AnnotationReader $annotationReader;
    private SorterFactory $sorterFactory;

    public function __construct(AnnotationReader $annotationReader, SorterFactory $sorterFactory)
    {
        $this->annotationReader = $annotationReader;
        $this->sorterFactory = $sorterFactory;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();
        $this->uow = $uow = $em->getUnitOfWork();

        $this->sortEntities($uow->getScheduledEntityInsertions(),self::ACTION_CREATE);
        $this->sortEntities($uow->getScheduledEntityUpdates(), self::ACTION_UPDATE);
        $this->sortEntities($uow->getScheduledEntityDeletions(), self::ACTION_DELETE);
    }

    private function sortEntities(array $entities, string $action)
    {
        foreach ($entities as $entity) {
            if($this->isSorted($entity)){
                $this->sortEntity($entity, $action);
            }
        }
    }

    private function isSorted($entity): bool
    {
        $classHasSortedAnnotation = $this->getSortedAnnotation($entity);
        if($classHasSortedAnnotation){
            return true;
        }
        return false;
    }

    private function getSortedAnnotation(object $entity): ?Sorted
    {
        $reflectionClass = new \ReflectionClass($entity);
        return $this->annotationReader->getClassAnnotation($reflectionClass, Sorted::class);
    }

    private function sortEntity($entity, string $action)
    {
        $sortedAnnotation = $this->getSortedAnnotation($entity);
        $sorter = $this->sorterFactory->make($sortedAnnotation);
        if($action == self::ACTION_CREATE){
            $sorter->add($entity);
        } else if($action == self::ACTION_UPDATE){
            $sorter->update($entity);
        } else if($action == self::ACTION_DELETE){
            $sorter->delete($entity);
        }
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [Events::onFlush];
    }

}
