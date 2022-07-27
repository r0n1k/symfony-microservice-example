<?php
namespace App\Services\EntitySorter\Sorters;

use App\Domain\Project\Entity\Conclusion\Paragraph\Order;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Services\EntitySorter\Annotation\Sorted;
use App\Services\EntitySorter\Repository\HierarchySorterRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;

class HierarchySorter implements ISorter
{
    private EntityManagerInterface $em;
    private UnitOfWork $uow;
    private HierarchySorterRepository $sorterRepository;
    private AnnotationReader $annotationReader;
    private EntityHelper $entityHelper;
    private Sorted $sortedAnnotation;
    private int $minSortNumber;
    private int $maxSortNumber;

    public function __construct(
        EntityManagerInterface $em,
        HierarchySorterRepository $sorterRepository,
        AnnotationReader $annotationReader,
        EntityHelper $entityHelper
    )
    {
        $this->em = $em;
        $this->uow = $em->getUnitOfWork();
        $this->sorterRepository = $sorterRepository;
        $this->annotationReader = $annotationReader;
        $this->entityHelper = $entityHelper;
    }

    public function add(object $entity)
    {
        $this->setSortedAnnotation($entity);
        $this->sorterRepository->setEntity($entity)->setSortedProperties($this->sortedAnnotation);
        $parentPropertyValue = $this->entityHelper->getEntityPropertyValue($entity, $this->sortedAnnotation->parent_property);
        $this->minSortNumber = $this->sorterRepository->getMin($parentPropertyValue);
        $this->maxSortNumber = $this->sorterRepository->getMax($parentPropertyValue);
        $countBro = $this->sorterRepository->getCount($parentPropertyValue);

        $sortOrder = (int)$this->entityHelper->getEntityPropertyValue($entity, $this->sortedAnnotation->property)->newValue;
        $parentProperty = $this->sortedAnnotation->parent_property;
        $sortedProperty = $this->sortedAnnotation->property;

        if ($this->minSortNumber === 0 && $this->maxSortNumber === 0 && $countBro === 0) {
            $sortOrder = 0;
        } else if ($sortOrder < $this->minSortNumber) {
            $sortOrder = $this->minSortNumber;
        } else if ($sortOrder > $this->maxSortNumber) {
            $sortOrder = $this->maxSortNumber + 1;
        }

        $entity = $this->entityHelper->setEntityPropertyValue($entity, $sortedProperty, $sortOrder);

        $notLastOrder = ($sortOrder != $this->maxSortNumber + 1);
        if($notLastOrder){
            //$parentPropertyValue = $this->entityHelper->getEntityPropertyValue($entity, $this->sortedAnnotation->parent_property);
            $this->sorterRepository->incrementOrdersAfterOrder($sortOrder, $parentPropertyValue->newValue);
        }
        $this->persist($entity);
    }

    private function setSortedAnnotation(object $entity)
    {
        $reflectionClass = new \ReflectionClass($entity);
        $this->sortedAnnotation = $this->annotationReader->getClassAnnotation($reflectionClass, Sorted::class);
    }

    public function update(object $entity)
    {
        $this->setSortedAnnotation($entity);

        if($this->isSoftRecovery($entity)){
            $this->add($entity);
            return;
        } else if($this->isSoftDeleting($entity)){
            $this->delete($entity);
            return;
        }
        $this->sorterRepository->setEntity($entity)->setSortedProperties($this->sortedAnnotation);
        try {
            $parentProperty = $this->entityHelper->getChangedEntityPropertyValue($entity, $this->sortedAnnotation->parent_property);
        } catch (\Exception $e) {
            $parentProperty = $this->entityHelper->getEntityPropertyValue($entity, $this->sortedAnnotation->parent_property);
        }

        $this->minSortNumber = $this->sorterRepository->getMin($parentProperty);
        $this->maxSortNumber = $this->sorterRepository->getMax($parentProperty);
        $countBro = $this->sorterRepository->getCount($parentProperty);

        try {
            $sortOrder = $this->entityHelper->getChangedEntityPropertyValue($entity, $this->sortedAnnotation->property);
        } catch (\Exception $e) {
            if($parentProperty->oldValue == $parentProperty->newValue){

                return;
            }
            $sortOrder = $this->entityHelper->getEntityPropertyValue($entity, $this->sortedAnnotation->property);
        }


        if ($this->minSortNumber === 0 && $this->maxSortNumber === 0 && $countBro === 0) {
            $sortOrder->newValue = 0;
        } else if ($sortOrder->newValue < $this->minSortNumber) {
            $sortOrder->newValue = $this->minSortNumber;
        } else if ($sortOrder->newValue > $this->maxSortNumber) {
            $sortOrder->newValue = $this->maxSortNumber + 1;
        }

        $entity = $this->entityHelper->setEntityPropertyValue(
            $entity,
            $this->sortedAnnotation->property,
            $sortOrder->newValue
        );


        if ($parentProperty->oldValue != $parentProperty->newValue) {
            $this->sorterRepository->decrementOrdersAfterOrder($sortOrder->oldValue, $parentProperty->oldValue);
            $this->sorterRepository->incrementOrdersAfterOrder($sortOrder->newValue, $parentProperty->newValue);
        } else {
            $parentProperty = $this->entityHelper->getEntityPropertyValue($entity, $this->sortedAnnotation->parent_property);
            if($sortOrder->newValue > $sortOrder->oldValue){
                $this->sorterRepository->updateOrdersWhenNewMore($sortOrder->oldValue, $sortOrder->newValue, $parentProperty->newValue);
            }

            if($sortOrder->newValue < $sortOrder->oldValue){
                $this->sorterRepository->updateOrdersWhenNewLess($sortOrder->oldValue, $sortOrder->newValue, $parentProperty->newValue);
            }
        }

        $this->persist($entity);
    }

    private function isSoftRecovery(object $entity): bool
    {
        if(empty($this->sortedAnnotation->deleted_property)){
            return false;
        }
        $deletedProperty = $this->entityHelper->getChangedEntityPropertyValue($entity, $this->sortedAnnotation->deleted_property);
        if($deletedProperty->oldValue == $this->sortedAnnotation->deleted_value && $deletedProperty->newValue != $this->sortedAnnotation->deleted_value){
            return true;
        }
        return false;
    }

    private function isSoftDeleting(object $entity): bool
    {
        if(empty($this->sortedAnnotation->deleted_property)){
            return false;
        }
        $deletedProperty = $this->entityHelper->getChangedEntityPropertyValue($entity, $this->sortedAnnotation->deleted_property);
        if($deletedProperty->oldValue != $this->sortedAnnotation->deleted_value && $deletedProperty->newValue == $this->sortedAnnotation->deleted_value){
            return true;
        }
        return false;
    }

    public function delete(object $entity)
    {
        $this->setSortedAnnotation($entity);
        $sortOrder = (int)$this->entityHelper->getEntityPropertyValue($entity, $this->sortedAnnotation->property)->newValue;
        $parentProperty = $this->entityHelper->getEntityPropertyValue($entity, $this->sortedAnnotation->parent_property);
        $this->sorterRepository->setEntity($entity)->setSortedProperties($this->sortedAnnotation);
        $this->sorterRepository->decrementOrdersAfterOrder($sortOrder, $parentProperty->newValue);
    }

    private function persist(object $entity)
    {
        $this->em->persist($entity);
        $this->uow->recomputeSingleEntityChangeSet($this->em->getClassMetadata(get_class($entity)), $entity);
    }
}
