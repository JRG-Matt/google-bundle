<?php

/*
 * This file is part of the BITGoogleBundle package. (c) bitgandtter <http://bitgandtter.github.com/> For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
namespace BIT\GoogleBundle\Google;

use Google_Service_Exception;
use Google_Service_Urlshortener;
use Google_Service_Urlshortener_Url;

/**
 * Implements Google URl Shorter.
 */
class GoogleURLShorter
{
    private $googleClient;

    public function __construct(GoogleClient $googleClient)
    {
        $this->googleClient = $googleClient->getWithoutAuthorization();
    }

    public function short($url)
    {
        try {
            // create url
            $urlShortener = new Google_Service_Urlshortener_Url();
            $urlShortener->longUrl = $url;

            // create service
            $googleServiceUrlshortener = new Google_Service_Urlshortener($this->googleClient);

            // short it
            $shortUrl = $googleServiceUrlshortener->url->insert($urlShortener);
            if (array_key_exists('id', $shortUrl)) {
                return $shortUrl['id'];
            }
        } catch (Google_Service_Exception $e) {
            return $e->getMessage();
            return null;
        }

        return null;
    }

    public function getClient()
    {
        return $this->googleClient;
    }
}
