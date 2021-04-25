<?php

namespace Bfy\SmartDeleteBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class SmartDeleteExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('smart_delete.entity.manager', $config['entity_manager']);
        $container->setParameter('smart_delete.entity.repository', $config['repository_namespace']);
        $container->setParameter('smart_delete.entity.row.dir', $config['entity_row']['dir']);
        $container->setParameter('smart_delete.entity.row.prefix', $config['entity_row']['prefix']);
        $container->setParameter('smart_delete.entity.row.dir', $config['entity_row']['dir']);
        $container->setParameter('smart_delete.entity.deleted.dir', $config['entity_deleted']['dir']);
        $container->setParameter('smart_delete.entity.deleted.prefix', $config['entity_deleted']['prefix']);
        $container->setParameter('smart_delete.entity.main.dir', $config['entity_main']['dir']);
        $container->setParameter('smart_delete.entity.main.prefix', $config['entity_main']['prefix']);
        $container->setParameter('smart_delete.entity.main.backup', $config['entity_main']['backup']);
    }
}
