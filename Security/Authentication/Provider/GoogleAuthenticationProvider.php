<?php

/*
 * This file is part of the BITGoogleBundle package.
 *
 * (c) bitgandtter <http://bitgandtter.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace BIT\GoogleBundle\Security\Authentication\Provider;

use BIT\GoogleBundle\Google\GoogleClient;
use BIT\GoogleBundle\Google\GoogleUser;
use BIT\GoogleBundle\Security\Authentication\Token\GoogleUserToken;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class GoogleAuthenticationProvider implements AuthenticationProviderInterface
{
    private $providerKey;
    private $googleClient;
    private $requestStack;
    private $userProvider;
    private $userChecker;

    public function __construct(
        $providerKey,
        GoogleClient $googleClient,
        RequestStack $requestStack,
        UserProviderInterface $userProvider = null,
        UserCheckerInterface $userChecker = null
    ) {
        if (null !== $userProvider && null === $userChecker) {
            throw new \InvalidArgumentException('$userChecker cannot be null, if $userProvider is not null.');
        }

        $this->providerKey = $providerKey;
        $this->googleClient = $googleClient;
        $this->requestStack = $requestStack;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
    }

    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }
        try {
            $this->googleClient->setup();
            $this->googleClient->authenticate($this->requestStack->getCurrentRequest()->get("code"));
            $this->googleClient->setAccessToken($this->googleClient->getAccessToken());

            $user = $token->getUser();

            if ($user instanceof UserInterface) {
                $this->userChecker->checkPostAuth($user);

                $newToken = new GoogleUserToken($this->providerKey, $user, $user->getRoles());
                $newToken->setAttributes($token->getAttributes());

                return $newToken;
            }

            $service = new GoogleUser($this->googleClient);
            $googlePlusPerson = $service->getInfo();

            if ($uid = $googlePlusPerson->getId()) {
                $this->googleClient->setPersistentData('access_token', $this->googleClient->getAccessToken());
                $this->googleClient->setPersistentData('user_id', $uid);

                $newToken = $this->createAuthenticatedToken($uid);
                $newToken->setAttributes($token->getAttributes());

                return $newToken;
            }

            throw new AuthenticationException('The Google user could not be retrieved from the session.');
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AuthenticationException($e->getMessage(), (int)$e->getCode(), $e);
        }
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof GoogleUserToken && $this->providerKey === $token->getProviderKey();
    }

    protected function createAuthenticatedToken($uid)
    {
        if (null === $this->userProvider) {
            return new GoogleUserToken($this->providerKey, $uid);
        }

        try {
            $user = $this->userProvider->loadUserByUsername($uid);
            $this->userChecker->checkPostAuth($user);
        } catch (UsernameNotFoundException $e) {
            if (!$this->createIfNotExists) {
                throw $e;
            }

            $user = $this->userProvider->createUserFromUid($uid);
        }

        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('User provider did not return an implementation of user interface.');
        }

        return new GoogleUserToken($this->providerKey, $user, $user->getRoles());
    }
}
