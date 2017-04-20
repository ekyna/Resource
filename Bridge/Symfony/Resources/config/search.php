<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Component\Resource\Bridge\Symfony\Elastica\SearchRepositoryFactory;
use Ekyna\Component\Resource\Search\Search;
use Ekyna\Component\Resource\Search\SearchRepositoryFactoryInterface;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Search repository factory
        ->set('ekyna_resource.factory.search_repository', SearchRepositoryFactory::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                abstract_arg('Repositories service locator'),           // Replaced by SearchExtension
                abstract_arg('Elastica indexes service locator'),       // Replaced by SearchExtension
                abstract_arg('Elastica transformers service locator'),  // Replaced by SearchExtension
                abstract_arg('Repositories classes'),                   // Replaced by SearchExtension
            ])
            ->call('setLocaleProvider', [service('ekyna_resource.provider.locale')])
            ->alias(SearchRepositoryFactoryInterface::class, 'ekyna_resource.factory.search_repository')

        // Search
        ->set('ekyna_resource.search', Search::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.factory.search_repository'),
                abstract_arg('The resources search configurations'),     // Replaced by SearchExtension
                service('ekyna_commerce.cache')->nullOnInvalid(),
            ])
            ->alias(Search::class, 'ekyna_resource.search')->public()
    ;
};
