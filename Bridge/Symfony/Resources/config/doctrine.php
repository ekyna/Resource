<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\ORM\Events;
use Ekyna\Component\Resource\Doctrine\DBAL\EventListener\EncryptorListener;
use Ekyna\Component\Resource\Doctrine\ORM\Factory\FactoryFactoryAdapter;
use Ekyna\Component\Resource\Doctrine\ORM\Listener\EntityListener;
use Ekyna\Component\Resource\Doctrine\ORM\Listener\LoadMetadataListener;
use Ekyna\Component\Resource\Doctrine\ORM\Listener\TranslatableListener;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerFactoryAdapter;
use Ekyna\Component\Resource\Doctrine\ORM\Manager\ManagerRegistry;
use Ekyna\Component\Resource\Doctrine\ORM\PersistenceHelper;
use Ekyna\Component\Resource\Doctrine\ORM\PersistenceTracker;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\RepositoryFactoryAdapter;
use Ekyna\Component\Resource\Persistence\PersistenceEventQueue;
use Ekyna\Component\Resource\Persistence\PersistenceHelperInterface;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Encryptor listener
        ->set('ekyna_resource.dbal.listener.encryptor', EncryptorListener::class)
            ->args([
                service('ekyna_resource.encryption.halite'),
            ])
            ->tag('doctrine.event_listener', [
                'event' => 'getEncryptor',
            ])

        // Manager Registry
        ->set('ekyna_resource.orm.manager_registry', ManagerRegistry::class)
            ->args([
                service('doctrine'),
            ])
            ->tag('doctrine.event_listener', [
                'event'      => Events::preFlush,
                'connection' => 'default',
                'priority'   => 1024, // Must be triggered before any other listeners
            ])
            ->tag('doctrine.event_listener', [
                'event'      => Events::postFlush,
                'connection' => 'default',
                'priority'   => 1024, // Must be triggered before any other listeners
            ])

        // Factory factory ORM adapter
        ->set('ekyna_resource.factory.factory.orm_adapter', FactoryFactoryAdapter::class)
            ->args([
                service('ekyna_resource.orm.manager_registry'),
            ])

        // Repository factory ORM adapter
        ->set('ekyna_resource.repository.factory.orm_adapter', RepositoryFactoryAdapter::class)
            ->args([
                service('ekyna_resource.orm.manager_registry'),
            ])

        // Manager factory ORM adapter
        ->set('ekyna_resource.manager.factory.orm_adapter', ManagerFactoryAdapter::class)
            ->args([
                service('ekyna_resource.orm.manager_registry'),
            ])

        // ORM Persistence Tracker
        ->set('ekyna_resource.orm.persistence_tracker', PersistenceTracker::class)
            ->args([
                service('ekyna_resource.orm.manager_registry'),
            ])
            ->tag('doctrine.event_listener', [
                'event'      => Events::postFlush,
                'connection' => 'default',
                'priority'   => 1024, // Must be triggered before any other listeners
            ])

        // ORM Persistence Event Queue
        ->set('ekyna_resource.orm.persistence_event_queue', PersistenceEventQueue::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.event_dispatcher'),
                service('ekyna_resource.orm.persistence_tracker'),
            ])

        // ORM Persistence Helper
        ->set('ekyna_resource.orm.persistence_helper', PersistenceHelper::class)
            ->args([
                service('ekyna_resource.orm.manager_registry'),
                service('ekyna_resource.orm.persistence_tracker'),
                service('ekyna_resource.orm.persistence_event_queue'),
            ])
            ->alias(PersistenceHelperInterface::class, 'ekyna_resource.orm.persistence_helper')

        // ORM Entity Listener
        ->set('ekyna_resource.orm.entity_listener', EntityListener::class)
            ->args([
                service('ekyna_resource.orm.persistence_event_queue'),
                service('ekyna_resource.behavior.executor'),
            ])
            ->tag('doctrine.event_listener', [
                'event'      => Events::onFlush,
                'connection' => 'default',
                'priority'   => 1024, // Must be triggered before any other listeners
            ])

        // ORM Metadata Listener
        ->set('ekyna_resource.orm.metadata_listener', LoadMetadataListener::class)
            ->args([
                service('ekyna_resource.behavior.executor'),
                abstract_arg('The resources entities classes'), // Replaced by RegistriesPass
            ])
            ->tag('doctrine.event_listener', [
                'event'      => Events::loadClassMetadata,
                'connection' => 'default',
                'priority'   => 768,
            ])

        // ORM Translatable Listener
        ->set('ekyna_resource.orm.translatable_listener', TranslatableListener::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.provider.locale'),
                abstract_arg('The resources translations classes map'), // Replaced by RegistriesPass
            ])
            ->tag('doctrine.event_listener', [
                'event'      => Events::loadClassMetadata,
                'connection' => 'default',
                'priority'   => 512,
            ])
            ->tag('doctrine.event_listener', [
                'event'      => Events::postLoad,
                'connection' => 'default',
                'priority'   => 512,
            ])
            ->tag('doctrine.orm.entity_listener')
    ;
};
