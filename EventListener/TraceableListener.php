<?php

namespace Librinfo\BaseEntitiesBundle\EventListener;

use DateTime;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;
use Monolog\Logger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Librinfo\BaseEntitiesBundle\EventListener\Traits\ClassChecker;

class TraceableListener implements LoggerAwareInterface, EventSubscriber
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * @var string
     */
    private $userClass;

    use ClassChecker;

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            'loadClassMetadata',
            'prePersist',
            'preUpdate'
        ];
    }

    /**
     * define Traceable mapping at runtime
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $metadata */
        $metadata = $eventArgs->getClassMetadata();

        if (!$this->hasTrait($metadata->getReflectionClass(), 'Librinfo\BaseEntitiesBundle\Entity\Traits\Traceable'))
            return; // return if current entity doesn't use Traceable trait

        $this->logger->debug(
            "[TraceableListner] Entering TraceableListner for « loadClassMetadata » event"
        );

        $this->logger->debug(
            "[TraceableListner] Using « " . $this->userClass . " » as User class"
        );

        // setting default mapping configuration for Traceable

        // createdAt
        $metadata->mapField([
            'fieldName' => 'createdAt',
            'type'      => 'datetime',
            'nullable'  => true
        ]);

        // updatedAt
        $metadata->mapField([
            'fieldName' => 'updatedAt',
            'type'      => 'datetime',
            'nullable'  => true
        ]);

        $this->logger->debug(
            "[TraceableListner] Added Traceable mapping metadata to Entity",
            ['class' => $metadata->getName()]
        );
    }

    /**
     * sets Traceable dateTime and user information when persisting entity
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();

        if (!$this->hasTrait($entity, 'Librinfo\BaseEntitiesBundle\Entity\Traits\Traceable'))
            return;

        $this->logger->debug(
            "[TraceableListner] Entering TraceableListner for « prePersist » event"
        );

        $now = new DateTime('NOW');
        $entity->setCreatedDate($now);
    }

    /**
     * sets Traceable dateTime and user information when updating entity
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();

        if (!$this->hasTrait($entity, 'Librinfo\BaseEntitiesBundle\Entity\Traits\Traceable'))
            return;

        $this->logger->debug(
            "[TraceableListner] Entering TraceableListner for « preUpdate » event"
        );

        $now = new DateTime('NOW');
        $entity->setLastUpdateDate($now);
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * setTokenStorage
     *
     * @param TokenStorage $tokenStorage
     */
    public function setTokenStorage(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string $userClass
     */
    public function setUserClass($userClass)
    {
        $this->userClass = $userClass;
    }
}
