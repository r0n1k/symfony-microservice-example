<?php
namespace App\Services\EntitySorter\Sorters;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;

class EntityHelper
{
    private EntityManagerInterface $em;
    private UnitOfWork $uow;
    private AnnotationReader $annotationReader;
    private object $entity;

    public function __construct(
        EntityManagerInterface $em,
        AnnotationReader $annotationReader
    )
    {
        $this->em = $em;
        $this->uow = $em->getUnitOfWork();
        $this->annotationReader = $annotationReader;
    }

    public function setEntityPropertyValue(object $entity, string $propertyName, $value): object
    {
        $this->entity = $entity;
        $setter = 'set' . ucfirst($propertyName);
        $entityClass = get_class($entity);
        if(!method_exists($entity, $setter)){
            throw new \Exception("Method '{$setter}' not exists in {$entityClass} class");
        }
        $value = $this->getConvertedToPhpPropertyValue($propertyName, $value);
        $entity->$setter($value);
        return $entity;
    }

    private function getConvertedToPhpPropertyValue(string $propertyName, $value)
    {
        $propertyDoctrineType = $this->getPropertyDoctrineTypeName($propertyName);
        $typeClass = \Doctrine\DBAL\Types\Type::getType($propertyDoctrineType);
        $dbPlatform = $this->em->getConnection()->getDatabasePlatform();
        $formattedValue = $typeClass->convertToPHPValue($value, $dbPlatform);
        return $formattedValue;
    }

    public function getEntityPropertyValue(object $entity, string $propertyName)
    {
        $this->entity = $entity;
        $meta = $this->em->getClassMetadata(get_class($this->entity));
        $notFormattedValue = $meta->getFieldValue($entity, $propertyName);
        $property = new SortedProperty($propertyName, $notFormattedValue, $notFormattedValue);
        return $this->getPropertyFormattedValue($property);
    }

    public function getChangedEntityPropertyValue(object $entity, string $propertyName): SortedProperty
    {
        $this->entity = $entity;
        $properties = $this->uow->getEntityChangeSet($entity);
        if(!array_key_exists($propertyName, $properties)){
            return $this->getEntityPropertyValue($entity, $propertyName);
        }
        $orderChangesSet = $properties[$propertyName];
        $property = new SortedProperty($propertyName, $orderChangesSet[0], $orderChangesSet[1]);
        return $this->getPropertyFormattedValue($property);
    }

    private function getPropertyFormattedValue(SortedProperty $property)
    {
        $property->oldValue = $this->getConvertedToDbPropertyValue($property->name, $property->oldValue);
        $property->newValue = $this->getConvertedToDbPropertyValue($property->name, $property->newValue);
        return $property;
    }

    private function getConvertedToDbPropertyValue(string $propertyName, $propertyValue)
    {
        if(empty($propertyValue)){
            return null;
        }
        $meta = $this->em->getClassMetadata(get_class($this->entity));
        $propertyDoctrineType = $meta->getTypeOfField($propertyName);
        if(!empty($propertyDoctrineType)){
            return $this->getConvertedToPhpByTypeValue($propertyDoctrineType, $propertyValue);
        }else if($meta->isSingleValuedAssociation($propertyName)){
            return $this->getEmbeddedEntityIdentifierValue($propertyValue);
        }
    }

    private function getEmbeddedEntityIdentifierValue($entity)
    {
        $embeddedEntityIdentifier = $this->getEntityIdentifier($entity);
        if(is_object($embeddedEntityIdentifier)){
            $formattedValue = $embeddedEntityIdentifier->getValue();
        }else{
            $formattedValue = (string)$embeddedEntityIdentifier;
        }
        return $formattedValue;
    }

    private function getEntityIdentifier(object $entity)
    {
        return $this->uow->getEntityIdentifier($entity)['id'];
    }

    private function getConvertedToPhpByTypeValue(string $doctrineType, $propertyValue)
    {
        $typeClass = \Doctrine\DBAL\Types\Type::getType($doctrineType);
        $dbPlatform = $this->em->getConnection()->getDatabasePlatform();
        return $typeClass->convertToDatabaseValue($propertyValue, $dbPlatform);
    }

    private function getPropertyDoctrineTypeName(string $propertyName)
    {
        $meta = $this->em->getClassMetadata(get_class($this->entity));
        if(array_key_exists($propertyName, $meta->fieldMappings)){
            return $meta->fieldMappings[$propertyName]['type'];
        } else {
            return null;
        }
    }
}
