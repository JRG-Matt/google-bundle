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

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class BITGoogleExtension extends Extension
{
    protected $resources = array('google' => 'google.xml', 'security' => 'security.xml');

    public function load(array $configs, ContainerBuilder $container)
    {
        $processor = new Processor();
        $configuration = new Configuration();

        $this->loadDefaults($container);
        $this->applyConfig($processor->processConfiguration($configuration, $configs), $container);
    }

    private function loadDefaults(ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        foreach ($this->resources as $resource) {
            $loader->load($resource);
        }
    }

    private function applyConfig(array $configs, ContainerBuilder $container)
    {
        if (isset($configs['alias'])) {
            $container->setAlias($configs['alias'], 'bit_google.client');
        }

        foreach (array('client', 'user', 'contact', 'url', 'helper', 'twig') as $attribute) {
            $container->setParameter('bit_google.' . $attribute . '.class', $configs['class'][$attribute]);
        }

        foreach (array(
                     'app_name',
                     'client_id',
                     'client_secret',
                     'simple_api_access',
                     'state',
                     'access_type',
                     'approval_prompt',
                     'scopes',
                     'callback_route'
                 ) as $attribute) {
            $container->setParameter('bit_google.' . $attribute, $configs[$attribute]);
        }
    }
}
