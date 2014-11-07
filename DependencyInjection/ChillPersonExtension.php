<?php

namespace Chill\PersonBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class ChillPersonExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $container->setParameter('cl_chill_person.search.use_double_metaphone', 
                $config['search']['use_double_metaphone']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    private function declarePersonAsCustomizable (ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (!isset($bundles['ChillCustomFieldsBundle'])) {
            throw new MissingBundleException('ChillCustomFieldsBundle');
        }

        $container->prependExtensionConfig('chill_custom_fields',
            array('customizables_entities' => 
                array(
                    array('class' => 'Chill\PersonBundle\Entity\Person', 'name' => 'PersonEntity')
                )
            )
        );
    }
    
    public function prepend(ContainerBuilder $container) 
    {
        $bundles = $container->getParameter('kernel.bundles');
        //add ChillMain to assetic-enabled bundles
        if (!isset($bundles['AsseticBundle'])) {
            throw new MissingBundleException('AsseticBundle');
        }

        $asseticConfig = $container->getExtensionConfig('assetic');
        $asseticConfig['bundles'][] = 'ChillPersonBundle';
        $container->prependExtensionConfig('assetic', 
                array('bundles' => array('ChillPersonBundle')));

        $this-> declarePersonAsCustomizable($container);
    }
}
