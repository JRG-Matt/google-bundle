<?php

/*
 * This file is part of the BITGoogleBundle package. (c) bitgandtter <http://bitgandtter.github.com/> For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
namespace BIT\GoogleBundle\Twig\Extension;

use BIT\GoogleBundle\Google\GoogleClient;
use BIT\GoogleBundle\Google\GoogleURLShorter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GoogleExtension extends \Twig_Extension
{
    private $container;
    private $googleClient;
    private $googleURLShortener;

    public function __construct(
        ContainerInterface $container,
        GoogleClient $googleClient,
        GoogleURLShorter $googleURLShortener
    ) {
        $this->container = $container;
        $this->googleClient = $googleClient;
        $this->googleURLShortener = $googleURLShortener;
    }

    public function getFunctions()
    {
        $extra = array('is_safe' => array('html'));

        $functions = array();
        $functions['google_login_url'] = new \Twig_Function_Method($this, 'renderLoginUrl', $extra);
        $functions['google_short_url'] = new \Twig_Function_Method($this, 'shortUrl', $extra);

        return $functions;
    }

    public function renderLoginUrl()
    {
        $this->googleClient->setup();

        return $this->googleClient->createAuthUrl();
    }

    public function shortUrl($url)
    {
        return $this->googleURLShortener->short($url);
    }

    public function getName()
    {
        return 'google';
    }
}
