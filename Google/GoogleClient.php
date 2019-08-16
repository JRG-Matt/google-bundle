<?php

/*
 * This file is part of the BITGoogleBundle package.
 *
 * (c) bitgandtter <http://bitgandtter.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace BIT\GoogleBundle\Google;

use Google_Auth_Exception;
use Google_Client;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;

/**
 * Implements Symfony2 service for Google_Client.
 */
class GoogleClient extends Google_Client
{

    const PREFIX = '_bit_google_';
    protected static $supportedKeys = array('access_token', 'user_id');
    private $config;
    private $session;
    private $router;
    private $prefix;

    public function __construct(array $config, Session $session, Router $router, $prefix = self::PREFIX)
    {
        parent::__construct();

        $this->config = $config;
        $this->session = $session;
        $this->router = $router;
        $this->prefix = $prefix;

        $this->setApplicationName($this->config ["app_name"]);
        $this->setClientId($this->config ["client_id"]);
        $this->setClientSecret($this->config ["client_secret"]);

        $this->setState($this->config ["state"]);
        $this->setAccessType($this->config ["access_type"]);
        $this->setApprovalPrompt($this->config ["approval_prompt"]);

        if ($this->config ["simple_api_access"]) {
            $this->setDeveloperKey($this->config ["simple_api_access"]);
        }
    }

    public function getWithoutAuthorization()
    {
        return new GoogleClient($this->config, $this->session, $this->router);
    }

    public function setup()
    {
        $scopes = array();
        $scopes[] = "openid";

        if ($this->config ["scopes"]["profile"]) {
            $scopes[] = "profile";
            $scopes[] = "https://www.googleapis.com/auth/plus.login";
            $scopes[] = "https://www.googleapis.com/auth/plus.me";
        }

        if ($this->config ["scopes"]["email"]) {
            $scopes[] = "email";
            $scopes[] = "https://www.googleapis.com/auth/plus.profile.emails.read";
        }

        if ($this->config ["scopes"]["contact"]) {
            $scopes[] = "https://www.google.com/m8/feeds";
        }

        $this->requestedScopes = $scopes;

        $this->setRedirectUri($this->router->generate($this->config ["callback_route"], array(), Router::ABSOLUTE_URL));

        $this->getAccessToken();
    }

    public function getAccessToken()
    {
        try {
            if ($this->getPersistentData('access_token')) {
                parent::setAccessToken($this->getPersistentData('access_token'));
            }

            return parent::getAccessToken();
        } catch (Google_Auth_Exception $e) {
            return null;
        }
    }

    /**
     * Get the data for $key
     *
     * @param string $key The key of the data to retrieve
     * @param null $default The default value to return if $key is not found
     *
     * @return mixed
     */
    public function getPersistentData($key, $default = null)
    {
        $this->checkSupportedKeys($key);

        return $this->session->get($this->constructSessionVariableName($key), $default);
    }

    private function checkSupportedKeys($key)
    {
        if (!in_array($key, self::$supportedKeys)) {
            throw new \Google_Exception('Unsupported key passed to getPersistentData.');
        }
    }

    private function constructSessionVariableName($key)
    {
        return $this->prefix . implode('_', array('g', $this->config ["client_id"], $key));
    }

    /**
     * Clear the data with $key from the persistent storage
     *
     * @param string $key
     *
     * @return void
     */
    public function clearPersistentData($key)
    {
        $this->checkSupportedKeys($key);
        $this->session->remove($this->constructSessionVariableName($key));
    }

    /**
     * Clear all data from the persistent storage
     *
     * @return void
     */
    public function clearAllPersistentData()
    {
        foreach ($this->session->all() as $k => $v) {
            if (0 !== strpos($k, $this->prefix)) {
                continue;
            }

            $this->session->remove($k);
        }
    }

    public function setAccessToken($accessToken)
    {
        parent::setAccessToken($accessToken);
        $this->setPersistentData('access_token', $accessToken);
    }

    /**
     * Stores the given ($key, $value) pair, so that future calls to
     * getPersistentData($key) return $value.
     * This call may be in another request.
     *
     * @param string $key
     * @param array $value
     *
     * @return void
     */
    public function setPersistentData($key, $value)
    {
        $this->checkSupportedKeys($key);
        $this->session->set($this->constructSessionVariableName($key), $value);
    }

}
