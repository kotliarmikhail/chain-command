<?php
declare(strict_types=1);

namespace App\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('chain_command');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('chain_commands')
            ->arrayPrototype()
            ->children()
            ->scalarNode('root_command')->isRequired()->end()
            ->arrayNode('member_commands')
            ->scalarPrototype()->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}