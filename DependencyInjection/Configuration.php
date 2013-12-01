<?php

namespace CL\Chill\PersonBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cl_chill_person');
        
        $rootNode
                ->canBeDisabled()
                ->children()
                ->arrayNode('search')
                    ->canBeDisabled()
                        ->children()
                        ->booleanNode('use_double_metaphone')
                            ->defaultFalse()
                            ->end()
                        ->booleanNode('use_trigrams')->defaultFalse()->end();
                

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.

        return $treeBuilder;
    }
}
