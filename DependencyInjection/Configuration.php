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
                                ->end() // use_double_metaphone, parent = children for 'search'
                            ->booleanNode('use_trigrams')
                                ->defaultFalse()
                                ->end() // use_trigrams, parent = children of 'search'
                        ->end() //children for 'search', parent = array node 'search'
                    ->end() // array 'search', parent = children of root
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
                            ->end() // birthdate_not_after, parent = children of validation
                                        
                        ->end() // children for 'validation', parent = validation
                    ->end() //validation, parent = children of root
                ->end() // children of root, parent = root
                    ->arrayNode('person_fields')
                        ->canBeDisabled()
                        ->children()
                                ->append($this->addFieldNode('place_of_birth'))
                                ->append($this->addFieldNode('email'))
                                ->append($this->addFieldNode('phonenumber'))
                                ->append($this->addFieldNode('nationality'))
                                ->append($this->addFieldNode('country_of_birth'))
                                ->append($this->addFieldNode('marital_status'))
                                ->append($this->addFieldNode('spoken_languages'))
                                ->append($this->addFieldNode('address'))
                        ->end() //children for 'person_fields', parent = array 'person_fields'
                    ->end() // person_fields, parent = children of root
                ->end() // children of 'root', parent = root
                ;
                

        return $treeBuilder;
    }
    
    private function addFieldNode($key)
    {
        $tree = new TreeBuilder();
        $node = $tree->root($key, 'enum');
        
        $node
                ->values(array('hidden', 'visible'))
                ->defaultValue('visible')
                ->info("If the field $key must be shown")
            ->end();
        //var_dump($node);
        return $node;
    }
}
