<?php

/*
 * This file is part of the desarrolla2 download bundle package
 *
 * Copyright (c) 2017-2018 Daniel González
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Daniel González <daniel@desarrolla2.com>
 */

namespace Desarrolla2\DownloadBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('download');

        $rootNode
            ->children()
                ->scalarNode('user')->isRequired()->end()
                ->scalarNode('host')->isRequired()->end()
                ->scalarNode('timeout')->defaultNull()->end()
                ->arrayNode('database')
                ->isRequired()
                    ->children()
                        ->scalarNode('directory')->isRequired()->end()
                        ->arrayNode('remote')
                            ->isRequired()
                            ->children()
                                ->scalarNode('host')->isRequired()->end()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('user')->isRequired()->end()
                                ->scalarNode('password')->isRequired()->end()
                                ->integerNode('port')->defaultNull()->end()
                            ->end()
                        ->end()
                        ->arrayNode('local')
                        ->isRequired()
                            ->children()
                                ->scalarNode('host')->isRequired()->end()
                                ->scalarNode('name')->isRequired()->end()
                                ->scalarNode('user')->isRequired()->end()
                                ->scalarNode('password')->isRequired()->end()
                                ->integerNode('port')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                    ->children()
                        ->arrayNode('only_structure')
                            ->treatNullLike([])
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('directories')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('remote')->isRequired()->end()
                            ->scalarNode('local')->isRequired()->end()
                            ->arrayNode('exclude')
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
