<?php
/**
 * Copyright (c) 2016. Lecturenotes.in
 * Proprietary and confidential
 */

/**
 * Created by PhpStorm.
 * User: amitosh
 * Date: 9/6/16
 * Time: 9:37 PM
 */

namespace RecaptchaBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('recaptcha');

        $rootNode
            ->children()
                ->scalarNode('site_key')->end()
                ->scalarNode('secret_key')->end()
            ->end();

        return $treeBuilder;
    }
}