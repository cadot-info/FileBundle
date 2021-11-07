<?php

namespace Cadotinfo\CrudBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class CadotinfoCrudExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        //charge config/packages/test.yaml par lib/Cadotinfo/tools-bundle/DependencyInjection/Configuration.php
        //$config = $this->processConfiguration(new Configuration(), $configs);
    }

    public function prepend(ContainerBuilder $container)
    {
    }

    public function getAlias()
    {
        return parent::getAlias();
    }
}
