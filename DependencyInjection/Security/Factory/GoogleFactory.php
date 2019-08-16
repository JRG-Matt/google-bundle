<?php

/*
 * This file is part of the BITGoogleBundle package.
 *
 * (c) bitgandtter <http://bitgandtter.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BIT\GoogleBundle\DependencyInjection\Security\Factory;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Reference;

class GoogleFactory extends AbstractFactory
{
    public function getPosition()
    {
        return 'pre_auth';
    }

    public function getKey()
    {
        return 'bit_google';
    }

    protected function getListenerId()
    {
        return 'bit_google.security.authentication.listener';
    }

    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        $authProviderId = 'bit_google.auth.' . $id;

        $definitionDecorator = new DefinitionDecorator('bit_google.auth');
        $definition = $container->setDefinition($authProviderId, $definitionDecorator);
        $definition->replaceArgument(0, $id);

        // with user provider
        if (isset($config['provider'])) {
            $definition->addArgument(new Reference($userProviderId));
            $definition->addArgument(new Reference('security.user_checker'));
        }

        return $authProviderId;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {
        $entryPointId = 'bit_google.security.authentication.entry_point.' . $id;
        $definitionDecorator = new DefinitionDecorator('bit_google.security.authentication.entry_point');
        $container->setDefinition($entryPointId, $definitionDecorator);

        // set options to container for use by other classes
        $container->setParameter('bit_google.options.' . $id, $config);

        return $entryPointId;
    }
}
