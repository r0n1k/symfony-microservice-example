<?php


namespace App\Services\EntityLogger;

use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\CustomDictionaryValue;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Dictionary\Dictionary;
use App\Http\Services\AuthorizedUserFactory;
use App\Services\EntityLogger\Annotation\Versioned;
use App\Services\EntityLogger\Entity\EntityLog;
use App\Services\EntityLogger\Repository\EntityLogRepository;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Metadata\MetadataFactory;

class EntityLoggerListener implements EventSubscriber
{
    const ACTION_CREATE = 'create';
    const ACTION_UPDATE = 'update';
    const ACTION_DELETE = 'delete';

    /** @var $uow UnitOfWork */
    private $uow;
    /** @var EntityManagerInterface */
    private $em;
    private EntityLogRepository $logRepository;
    private AnnotationReader $annotationReader;
    private SerializerInterface $serializer;
    private AuthorizedUserFactory $userFactory;

    public function __construct(
        EntityLogRepository $logRepository,
        AnnotationReader $annotationReader,
        SerializerBuilder $serializerBuilder,
        AuthorizedUserFactory $userFactory
    )
    {
        $this->logRepository = $logRepository;
        $this->annotationReader = $annotationReader;
        $this->serializer = $serializerBuilder->build();
        $this->userFactory = $userFactory;
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $this->em = $em = $args->getEntityManager();
        $this->uow = $uow = $em->getUnitOfWork();

        $this->formatEntities($uow->getScheduledEntityInsertions(),self::ACTION_CREATE);
        $this->formatEntities($uow->getScheduledEntityUpdates(), self::ACTION_UPDATE);
        $this->formatEntities($uow->getScheduledEntityDeletions(), self::ACTION_DELETE);
    }

    private function formatEntities(array $entities, string $action)
    {
        foreach ($entities as $entity) {
            if($this->isLoggable($entity)){
                $this->formatEntity($entity, $action);
            }
        }
    }

    private function isLoggable($entity): bool
    {
        $loggableEntities = $this->getLoggableEntities();
        if(in_array(get_class($entity), $loggableEntities)){
            return true;
        }
        return false;
    }

    private function getLoggableEntities()
    {
        return [
            Paragraph::class,
            Block::class,
            Dictionary::class,
            CustomDictionaryValue::class
        ];
    }

    private function formatEntity($entity, string $action)
    {
        $formattedValues = [];
        foreach ($this->uow->getEntityChangeSet($entity) as $propertyName => $changes) {
            // changes[0] - old value, changes[1] - new value
            $notFormattedNewValue = $changes[1];
            if($this->notNeedLogProperty($entity, $propertyName, $changes[0], $changes[1])){
                continue;
            }

            $formattedValue = $this->formatPropertyValue($entity, $propertyName, $notFormattedNewValue);
            $formattedValues[$propertyName] = $formattedValue;
        }

        $this->createNewLog($entity, $action, $formattedValues);
    }

    private function notNeedLogProperty(object $entity, string $propertyName, $notFormattedOldValue = null, $notFormattedNewValue = null)
    {
        $formattedOldValue = $this->formatPropertyValue($entity, $propertyName, $notFormattedOldValue);
        $formattedNewValue = $this->formatPropertyValue($entity, $propertyName, $notFormattedNewValue);
        $valueNotBeChanged = ($formattedOldValue == $formattedNewValue);
        $propertyWithoutVersionedAnnotation = empty($this->getPropertyVersionedAnnotation($entity, $propertyName));
        if ($propertyWithoutVersionedAnnotation || $valueNotBeChanged) {
            return true;
        }
        return false;
    }

    private function getPropertyVersionedAnnotation(object $entity, string $propertyName): ?Versioned
    {
        $reflectionClass = new \ReflectionClass(get_class($entity));
        if(!$reflectionClass->hasProperty($propertyName)){
            return null;
        }
        $reflectionProperty = $reflectionClass->getProperty($propertyName);
        $annotation = $this->annotationReader->getPropertyAnnotation($reflectionProperty, Versioned::class);
        return $annotation;
    }

    private function formatPropertyValue($entity, $propertyName, $notFormattedValue)
    {
        $meta = $this->em->getClassMetadata(get_class($entity));
        if (empty($notFormattedValue)) {
            return null;
        }
        $doctrineTypeName = $this->getPropertyDoctrineTypeName($meta, $propertyName);
        if($meta->isSingleValuedAssociation($propertyName)){
            $formattedValue = $this->getEmbeddedEntityIdentifierValue($notFormattedValue);
        }else if(!empty($doctrineTypeName)){
            $formattedValue = $this->getConvertedToDbPropertyValue($doctrineTypeName, $notFormattedValue);
        }else if($this->isEmbeddableEntity($meta, $propertyName)){
            $formattedValue = $this->serializer->serialize($notFormattedValue, 'json');
        }
        return $formattedValue;
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
        // если не составной ключ(а у нас таких вроде нет), значение всегда в id элементе
        return $this->uow->getEntityIdentifier($entity)['id'];
    }

    private function getPropertyDoctrineTypeName(ClassMetadata $meta, string $propertyName)
    {
        if(array_key_exists($propertyName, $meta->fieldMappings)){
            return $meta->fieldMappings[$propertyName]['type'];
        } else {
            return null;
        }
    }

    private function getConvertedToDbPropertyValue(string $propertyDoctrineType, $notFormattedValue)
    {
        $typeClass = \Doctrine\DBAL\Types\Type::getType($propertyDoctrineType);
        $dbPlatform = $this->em->getConnection()->getDatabasePlatform();
        $formattedValue = $typeClass->convertToDatabaseValue($notFormattedValue, $dbPlatform);
        return $formattedValue;
    }

    private function isEmbeddableEntity(ClassMetadata $meta, string $propertyName)
    {
        $metadataEmbeddedClassesNotEmpty = (count($meta->embeddedClasses) > 0);
        $propertyContainsInEmbeddedClasses = array_key_exists($propertyName, $meta->embeddedClasses);
        if($metadataEmbeddedClassesNotEmpty && $propertyContainsInEmbeddedClasses){
            return true;
        }
        return false;
    }

    private function createNewLog(object $entity, string $action, array $formattedValues)
    {
        $log = $this->fillEntity($entity, $action, $formattedValues);
        $this->persist($log);
    }

    private function fillEntity(object $entity, string $action, array $formattedValues): EntityLog
    {
        $userId = ($user = $this->userFactory->getUser()) ? $user->getId()->getValue() : 0;

        $log = new EntityLog();
        $log->setAction($action);
        $log->setLoggedAt();
        $log->setObjectId((string)$this->getEntityIdentifier($entity));
        $log->setObjectClass(get_class($entity));
        $log->setVersion($this->logRepository->getLastVersionByEntity($entity) + 1);
        $log->setData($formattedValues);
        $log->setUsername($userId);

        return $log;
    }

    private function persist(EntityLog $log)
    {
        $this->em->persist($log);
        $this->uow->computeChangeSet($this->em->getClassMetadata(get_class($log)), $log);
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents()
    {
        return [Events::onFlush];
    }

}
