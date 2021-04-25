<?php

namespace Bfy\SmartDeleteBundle\EventSubscriber;

use Bfy\SmartDeleteBundle\Entity\SmartDeleteInterface;
use Bfy\SmartDeleteBundle\Helper\DeletedTrait;

use Doctrine\Common\EventSubscriber;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;

use Symfony\Component\DependencyInjection\ContainerInterface;

use DateTime;
use ReflectionClass;
use ReflectionException;

class DatabaseActivitySubscriber implements EventSubscriber
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var string */
    private $deletedNamespace;

    /**
     * DatabaseActivitySubscriber constructor.
     *
     * @tips: Use ManagerRegistry instead EntityManager to get an objectManager of specific doctrine connection
     *
     * @param ManagerRegistry $managerRegistry
     * @param ContainerInterface $configuration
     */
    public function __construct(ManagerRegistry $managerRegistry, ContainerInterface $configuration)
    {
        $entityManagerName = $configuration->getParameter('smart_delete.entity.manager');

        $this->objectManager = $managerRegistry->getManager($entityManagerName);

        $this->deletedNamespace = $configuration->getParameter('smart_delete.entity.deleted.prefix');
    }

    /**
     * @inheritDoc
     * @return array|string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    /**
     * Call before remove doctrine entity
     *
     * @param LifecycleEventArgs $args
     * @throws ReflectionException
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        $reflectionClass = new ReflectionClass($entity);
        $shortName = $reflectionClass->getShortName();
        $classNamespace = "{$this->deletedNamespace}\\{$shortName}Deleted";

        /** @var SmartDeleteInterface & DeletedTrait $class */
        $class = new $classNamespace();
        $class->loadFrom($entity);
        $class->setDeletedAt(new DateTime());

        $this->objectManager->persist($class);
        $this->objectManager->flush();
    }
}
