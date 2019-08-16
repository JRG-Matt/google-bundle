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

use Google_Service_Plus;

/**
 * Implements Symfony2 service for Google authenticated user info.
 */
class GoogleUser
{
    private $googleClient;

    public function __construct(GoogleClient $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    public function getInfo()
    {
        $googleServicePlus = new Google_Service_Plus($this->googleClient);

        return $googleServicePlus->people->get('me');
    }

    public function getClient()
    {
        return $this->googleClient;
    }
}
