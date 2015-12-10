<?php

namespace Chill\PersonBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Chill\MainBundle\DependencyInjection\MissingBundleException;

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
        
        // set configuration for double metaphone
        $container->setParameter('cl_chill_person.search.use_double_metaphone', 
                $config['search']['use_double_metaphone']);
        
        // set configuration for validation
        $container->setParameter('chill_person.validation.birtdate_not_before',
                $config['validation']['birthdate_not_after']);

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
        $this->prependRoleHierarchy($container);
                
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
        
        //declare routes for person bundle
         $container->prependExtensionConfig('chill_main', array(
           'routing' => array(
              'resources' => array(
                 '@ChillPersonBundle/Resources/config/routing.yml'
              )
           )
        ));
    }
    
    protected function prependRoleHierarchy(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('security', array(
           'role_hierarchy' => array(
              'CHILL_PERSON_UPDATE' => array('CHILL_PERSON_SEE'),
              'CHILL_PERSON_CREATE' => array('CHILL_PERSON_SEE')
           )
        ));
    }
}
