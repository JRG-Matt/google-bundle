<?php

/*
 * This file is part of the BITGoogleBundle package.
 *
 * (c) bitgandtter <http://bitgandtter.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace BIT\GoogleBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * Generates the configuration tree.
     *
     * @return TreeBuilder
     */

    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bit_google');

        $rootNode->fixXmlConfig('permission', 'permissions')->children() // children
            ->scalarNode('app_name')->isRequired()->cannotBeEmpty()->end() // app name
            ->scalarNode('client_id')->isRequired()->cannotBeEmpty()->end() // client id
            ->scalarNode('client_secret')->isRequired()->cannotBeEmpty()->end() // client secret
            ->scalarNode('simple_api_access')->cannotBeEmpty()->defaultNull()->end() // simple API access
            ->scalarNode('callback_route')->isRequired()->cannotBeEmpty()->end() // redirect callback
            ->arrayNode('scopes')->addDefaultsIfNotSet()->children() // scopes by api
            ->booleanNode('profile')->defaultFalse()->end() // profile
            ->booleanNode('email')->defaultFalse()->end() // email
            ->booleanNode('contact')->defaultFalse()->end() // contact
            ->end()->end() // end scopes by api
            ->scalarNode('state')->defaultValue('auth')->end() // default state auth
            ->scalarNode('access_type')->defaultValue('online')->end() // default access type online
            ->scalarNode('approval_prompt')->defaultValue('auto')->end() //
            ->arrayNode('class')->addDefaultsIfNotSet()->children() // classes
            ->scalarNode('client')->defaultValue('BIT\GoogleBundle\Google\GoogleClient')->end() // api
            ->scalarNode('user')->defaultValue('BIT\GoogleBundle\Google\GoogleUser')->end() // user
            ->scalarNode('contact')->defaultValue('BIT\GoogleBundle\Google\GoogleContact')->end() // contact
            ->scalarNode('url')->defaultValue('BIT\GoogleBundle\Google\GoogleURLShorter')->end() // url
            ->scalarNode('helper')->defaultValue('BIT\GoogleBundle\Templating\Helper\GoogleHelper')->end() // template helper
            ->scalarNode('twig')->defaultValue('BIT\GoogleBundle\Twig\Extension\GoogleExtension')->end() // twig ext
            ->end() // end classes
            ->end()->end();

        return $treeBuilder;
    }
}
