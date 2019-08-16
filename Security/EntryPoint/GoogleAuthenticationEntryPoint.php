<?php

/*
 * This file is part of the BITGoogleBundle package.
 *
 * (c) bitgandtter <http://bitgandtter.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace BIT\GoogleBundle\Security\EntryPoint;

use BIT\GoogleBundle\Google\GoogleClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * GoogleAuthenticationEntryPoint starts an authentication via Google.
 *
 */
class GoogleAuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private $googleApi;

    public function __construct(GoogleClient $googleApi)
    {
        $this->googleApi = $googleApi;
    }

    /**
     * {@inheritdoc}
     */

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->googleApi->createAuthUrl());
    }
}
