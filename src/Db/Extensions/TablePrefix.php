<?php


namespace App\Db\Extensions;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class TablePrefix implements EventSubscriber
{

    protected $prefix;

    public function __construct(string $prefix)
    {
        $this->prefix = $prefix;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();

        if (!$classMetadata->isInheritanceTypeSingleTable()
            || $classMetadata->getName() === $classMetadata->rootEntityName
        ) {
            $classMetadata->setPrimaryTable([
                'name' => $this->prefix . $classMetadata->getTableName(),
            ]);
        }

        foreach ($classMetadata->getAssociationMappings() as $fieldName => $mapping) {
            if ($mapping['type'] == ClassMetadataInfo::MANY_TO_MANY && $mapping['isOwningSide']) {
                $mappedTableName = $mapping['joinTable']['name'];
                $classMetadata->associationMappings[$fieldName]['joinTable']['name'] = $this->prefix . $mappedTableName;
            }
        }
    }

   /**
    * @inheritDoc
    */
   public function getSubscribedEvents()
   {
      return ['loadClassMetadata'];
   }
}
