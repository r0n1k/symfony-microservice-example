<?php
namespace App\Services\EntitySorter\Repository;

use App\Domain\Project\Entity\Conclusion\Pdf\Pdf;
use App\Services\EntitySorter\Annotation\Sorted;
use App\Services\EntitySorter\Sorters\EntityHelper;
use App\Services\EntitySorter\Sorters\SortedProperty;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\OrderBy;
use Doctrine\ORM\QueryBuilder;

class HierarchySorterRepository
{
    private EntityManagerInterface $em;
    private object $entity;
    private Sorted $sortedAnnotation;
    private EntityHelper $entityHelper;

    public function __construct(EntityManagerInterface $em, EntityHelper $entityHelper)
    {
        $this->em = $em;
        $this->entityHelper = $entityHelper;
    }

    public function setEntity(object $entity): HierarchySorterRepository
    {
        $this->entity = $entity;
        return $this;
    }

    public function setSortedProperties(Sorted $sortedAnnotation): HierarchySorterRepository
    {
        $this->sortedAnnotation = $sortedAnnotation;
        return $this;
    }

    public function getMax(SortedProperty $parentProperty): int
    {
        $entityClass = get_class($this->entity);
        $qb = $this->em->createQueryBuilder()
            ->select("MAX(e.{$this->sortedAnnotation->property}) AS max_order")
            ->from($entityClass, 'e');
        $qb->where($this->getNotDeletedDql());
        $qb->andWhere($this->getSamePropertiesDql());
        if(empty($parentProperty->newValue)){
            $qb->andWhere("e.{$this->sortedAnnotation->parent_property} is NULL");
        }else{
            $qb->andWhere("e.{$this->sortedAnnotation->parent_property} = :parent_property_value")
                ->setParameter(':parent_property_value', $parentProperty->newValue);
        }

        $qb->orderBy(new OrderBy('max_order', 'desc'))
            ->setMaxResults(1);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function getCount(SortedProperty $parentProperty): int
    {
        $entityClass = get_class($this->entity);
        $qb = $this->em->createQueryBuilder()
            ->select("MAX(e.{$this->sortedAnnotation->property}) AS max_order")
            ->from($entityClass, 'e');
        $qb->where($this->getNotDeletedDql());
        $qb->andWhere($this->getSamePropertiesDql());
        if(empty($parentProperty->newValue)){
            $qb->andWhere("e.{$this->sortedAnnotation->parent_property} is NULL");
        }else{
            $qb->andWhere("e.{$this->sortedAnnotation->parent_property} = :parent_property_value")
                ->setParameter(':parent_property_value', $parentProperty->newValue);
        }
        $qb->orderBy(new OrderBy('max_order', 'desc'));

        return count($qb->getQuery()->getArrayResult());
    }

    private function getSamePropertiesDql()
    {
        if(!isset($this->sortedAnnotation->same_properties)){
            return "2=2";
        }
        $propertyName = $this->sortedAnnotation->same_properties;
        $property = $this->entityHelper->getEntityPropertyValue($this->entity, $propertyName);
        $propertyValue = $property->newValue;
        if(empty($propertyValue)){
            return "e.{$propertyName} is NULL";
        }

        if(is_int($propertyValue)){
            $propertyValue = (int)$propertyValue;
        }else if(is_string($propertyValue)){
            $propertyValue = "'{$propertyValue}'";
        }
        return "e.{$propertyName} = $propertyValue";
    }

    public function getMin(SortedProperty $parentProperty): int
    {
        $entityClass = get_class($this->entity);
        $qb = $this->em->createQueryBuilder()
            ->select("MIN(e.{$this->sortedAnnotation->property}) AS min_order")
            ->from($entityClass, 'e');

        $qb->where($this->getNotDeletedDql());
        $qb->andWhere($this->getSamePropertiesDql());
        if(empty($parentProperty->newValue)){
            $qb->andWhere("e.{$this->sortedAnnotation->parent_property} is NULL");
        }else{
            $qb->andWhere("e.{$this->sortedAnnotation->parent_property} = :parent_property_value")
                ->setParameter(':parent_property_value', $parentProperty->newValue);
        }

        $qb->orderBy(new OrderBy('min_order', 'asc'))
            ->setMaxResults(1);

        return (int)$qb->getQuery()->getSingleScalarResult();
    }

    public function incrementOrdersAfterOrder(int $sortOrder, $parentPropertyValue)
    {
        $entityClass = get_class($this->entity);
        $propertyName = $this->sortedAnnotation->property;
        $parentPropertyName = $this->sortedAnnotation->parent_property;
        $parentPropertyEqual = empty($parentPropertyValue) ? "IS NULL" : "= {$parentPropertyValue}";

        $this->em->createQuery("
                    UPDATE {$entityClass} e
                    SET e.{$propertyName} = e.{$propertyName}+1
                    WHERE e.{$propertyName} >= {$sortOrder} AND
                    e.{$parentPropertyName} {$parentPropertyEqual} AND
                    {$this->getNotDeletedDql()} AND
                    {$this->getSamePropertiesDql()}
            ")->execute();
    }

    private function getNotDeletedDql()
    {
        if(isset($this->sortedAnnotation->property)){
            return "1=1";
        }
        $value = $this->sortedAnnotation->deleted_value;
        if(is_int($value)){
            $value = (int)$value;
        }else if(is_string($value)){
            $value = "'{$value}'";
        }

        return empty($this->sortedAnnotation->deleted_property) ?
            "1=1" : 'e.'.$this->sortedAnnotation->deleted_property.' != '.$value;
    }

    public function decrementOrdersAfterOrder(int $sortOrder, $parentPropertyValue)
    {
        $entityClass = get_class($this->entity);
        $propertyName = $this->sortedAnnotation->property;
        $parentPropertyName = $this->sortedAnnotation->parent_property;
        $parentPropertyEqual = empty($parentPropertyValue) ? "IS NULL" : "= {$parentPropertyValue}";

        $this->em->createQuery("
                    UPDATE {$entityClass} e
                    SET e.{$propertyName} = e.{$propertyName}-1
                    WHERE e.{$propertyName} > {$sortOrder} AND
                    e.{$parentPropertyName} {$parentPropertyEqual} AND
                    {$this->getNotDeletedDql()} AND
                    {$this->getSamePropertiesDql()}
            ")->execute();
    }

    public function updateOrdersWhenNewMore(int $oldPosition, int $newPosition, $parentPropertyValue)
    {
        $entityClass = get_class($this->entity);
        $sortOrder = $this->sortedAnnotation->property;
        $parent = $this->sortedAnnotation->parent_property;
        $parentPropertyEqual = empty($parentPropertyValue) ? "IS NULL" : "= {$parentPropertyValue}";

        $this->em->createQuery("
                    UPDATE {$entityClass} e
                    SET e.{$sortOrder} = e.{$sortOrder}-1
                    WHERE
                    e.{$sortOrder} > {$oldPosition} AND
                    e.{$sortOrder} <= {$newPosition} AND
                    e.{$parent} {$parentPropertyEqual} AND
                    {$this->getNotDeletedDql()} AND
                    {$this->getSamePropertiesDql()}
            ")->execute();
    }

    public function updateOrdersWhenNewLess(int $oldPosition, int $newPosition, $parentPropertyValue)
    {
        $entityClass = get_class($this->entity);
        $sortOrder = $this->sortedAnnotation->property;
        $parent = $this->sortedAnnotation->parent_property;
        $parentPropertyEqual = empty($parentPropertyValue) ? "IS NULL" : "= {$parentPropertyValue}";

        $this->em->createQuery("
                    UPDATE {$entityClass} e
                    SET e.{$sortOrder} = e.{$sortOrder}+1
                    WHERE
                    e.{$sortOrder} < {$oldPosition} AND
                    e.{$sortOrder} >= {$newPosition} AND
                    e.{$parent} {$parentPropertyEqual} AND
                    {$this->getNotDeletedDql()} AND
                    {$this->getSamePropertiesDql()}
            ")->execute();
    }
}
