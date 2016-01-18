<?php

namespace Librinfo\BaseEntitiesBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Id\UuidGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Librinfo\BaseEntitiesBundle\EventListener\Traits\ClassChecker;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class GuidableListener implements EventSubscriber
{
    use ClassChecker;

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata'
        ];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();

        // Do not generate id mapping twice for entities that extend a MappedSuperclass
        if ($metadata->isMappedSuperclass)
            return;

        $reflectionClass = $metadata->getReflectionClass();

        // return if the current entity doesn't use Guidable trait
        if ( !$reflectionClass || !$this->hasTrait($reflectionClass, 'Librinfo\BaseEntitiesBundle\Entity\Traits\Guidable') )
            return;

        $metadata->mapField([
            'id' => true,
            'fieldName' => "id",
            'type' => "guid",
            'columnName' => "id",
        ]);
        $metadata->setIdGenerator(new UuidGenerator());
    }
}
