<?php

namespace Chill\PersonBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    
    private $validationBirthdateNotAfterInfos = "The period before today during which"
            . " any birthdate is not allowed. The birthdate is expressed as ISO8601 : "
            . "https://en.wikipedia.org/wiki/ISO_8601#Durations";
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
                        ->booleanNode('use_trigrams')
                            ->defaultFalse()
                            ->end()
                    ->end()
                ->end()
                ->arrayNode('validation')
                    ->canBeDisabled()
                    ->children()
                        ->scalarNode('birthdate_not_after')
                        ->info($this->validationBirthdateNotAfterInfos)
                        ->defaultValue('P1D')
                        ->validate()
                            ->ifTrue(function($period) {
                                try {
                                    $interval = new \DateInterval($period);
                                } catch (\Exception $ex) {
                                    return true;
                                }   
                                return false;
                            })
                            ->thenInvalid('Invalid period for birthdate validation : "%s" '
                                    . 'The parameter should match duration as defined by ISO8601 : '
                                    . 'https://en.wikipedia.org/wiki/ISO_8601#Durations')
                        ->end()
                    ->end()
                ->end();
                

        return $treeBuilder;
    }
}
