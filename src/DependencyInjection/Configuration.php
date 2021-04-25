<?php

namespace Bfy\SmartDeleteBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('smart_delete');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('entity_manager')
                    ->info('The name of entity manager to use')
                    ->defaultValue('default')
                    ->end()

                ->scalarNode('repository_namespace')
                    ->info('The repository namespace')
                    ->defaultValue('deleted')
                    ->end()

                ->arrayNode('entity_row')->info('Settings for EntityRow')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('dir')->info('Folder to create EntityRow')->defaultValue('%kernel.project_dir%/src/Entity/Row')->end()
                            ->scalarNode('prefix')->info('Namespace of EntityRow')->defaultValue('App\Entity\Row')->end()
                        ->end()
                    ->end()

                ->arrayNode('entity_deleted')->info('Settings for EntityDeleted')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('dir')->info('Folder to create EntityDeleted')->defaultValue('%kernel.project_dir%/src/Entity/Deleted')->end()
                            ->scalarNode('prefix')->info('Namespace of EntityDeleted')->defaultValue('App\Entity\Deleted')->end()
                        ->end()
                    ->end()


                ->arrayNode('entity_main')->info('Setting for main Entity')
                    ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('dir')->info('Folder to create Entity')->defaultValue('%kernel.project_dir%/src/Entity/Model')->end()
                            ->scalarNode('prefix')->info('Namespace of Entity')->defaultValue('App\Entity\Model')->end()
                            ->scalarNode('backup')->info('Folder to save original doctrine entity')->defaultValue('%kernel.project_dir%/entity_backup')->end()
                        ->end()
                    ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
