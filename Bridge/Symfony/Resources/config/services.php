<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\ResourceBundle\Dispatcher\ResourceEventDispatcher;
use Ekyna\Component\Resource\Bridge\Symfony\Locale\RequestLocaleProvider;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Encryption\EncryptorInterface;
use Ekyna\Component\Resource\Encryption\HaliteEncryptor;
use Ekyna\Component\Resource\Event\EventQueue;
use Ekyna\Component\Resource\Factory\FactoryFactory;
use Ekyna\Component\Resource\Factory\FactoryFactoryInterface;
use Ekyna\Component\Resource\Locale\LocaleProviderInterface;
use Ekyna\Component\Resource\Manager\ManagerFactory;
use Ekyna\Component\Resource\Manager\ManagerFactoryInterface;
use Ekyna\Component\Resource\Repository\RepositoryFactory;
use Ekyna\Component\Resource\Repository\RepositoryFactoryInterface;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Factory factory
        ->set('ekyna_resource.factory.factory', FactoryFactory::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                abstract_arg('Factories services locator'),
            ])
            ->call('setLocaleProvider', [service('ekyna_resource.provider.locale')])
            ->alias(FactoryFactoryInterface::class, 'ekyna_resource.factory.factory')

        // Repository factory
        ->set('ekyna_resource.repository.factory', RepositoryFactory::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                abstract_arg('Factories services locator'),
            ])
            ->call('setLocaleProvider', [service('ekyna_resource.provider.locale')])
            ->call('setResultCache', [service('doctrine.orm.default_result_cache')])
            ->alias(RepositoryFactoryInterface::class, 'ekyna_resource.repository.factory')

        // Manager factory
        ->set('ekyna_resource.manager.factory', ManagerFactory::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                abstract_arg('Factories services locator'),
                service('ekyna_resource.event_dispatcher'),
                param('kernel.debug'),
            ])
            ->alias(ManagerFactoryInterface::class, 'ekyna_resource.manager.factory')

        // Locale provider
        ->set('ekyna_resource.provider.locale', RequestLocaleProvider::class)
            ->args([
                param('ekyna_resource.locales'),
                param('kernel.default_locale'),
            ])
            ->tag('kernel.event_subscriber', ['priority' => 98])
            ->alias(LocaleProviderInterface::class, 'ekyna_resource.provider.locale')

        // Resource event dispatcher
        ->set('ekyna_resource.event_dispatcher', ResourceEventDispatcher::class)
            ->call('setConfigurationRegistry', [service('ekyna_resource.registry.resource')])
            ->call('setEventQueue', [service('ekyna_resource.event_queue')])
            ->alias(ResourceEventDispatcherInterface::class, 'ekyna_resource.event_dispatcher')

        // Resource event queue
        ->set('ekyna_resource.event_queue', EventQueue::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.event_dispatcher'),
            ])

        // Halite encryptor
        ->set('ekyna_resource.encryption.halite', HaliteEncryptor::class)
            ->args([
                expr("parameter('kernel.project_dir')~'/var/data/encryption/halite.key'"),
            ])
            ->alias(EncryptorInterface::class, 'ekyna_resource.encryption.halite')
    ;
};
