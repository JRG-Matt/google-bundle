<?php

/*
 * This file is part of the BITGoogleBundle package.
 *
 * (c) bitgandtter <http://bitgandtter.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace BIT\GoogleBundle\Security\Firewall;

use BIT\GoogleBundle\Security\Authentication\Token\GoogleUserToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Firewall\AbstractAuthenticationListener;

/**
 * Google authentication listener.
 */
class GoogleFirewallListener extends AbstractAuthenticationListener
{

    protected function attemptAuthentication(Request $request)
    {
        return $this->authenticationManager->authenticate(new GoogleUserToken($this->providerKey));
    }
}
