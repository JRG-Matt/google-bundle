<?php

/*
 * This file is part of the BITGoogleBundle package. (c) bitgandtter <http://bitgandtter.github.com/> For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */
namespace BIT\GoogleBundle\Google;

use Google_Auth_Exception;
use Google_Http_Request;

/**
 * Implements Google Contact.
 */
class GoogleContact
{
    const CONTACTS_API_URL = 'https://www.google.com/m8/feeds/contacts/default/full?';

    private $googleClient;

    public function __construct(GoogleClient $googleClient)
    {
        $this->googleClient = $googleClient;
    }

    public function getClient()
    {
        return $this->googleClient;
    }

    public function getContacts($startIndex = null, $maxResults = 9999)
    {
        $urlParams = array();

        if (null !== $startIndex) {
            $urlParams['start-index'] = $startIndex;
        }

        if (null !== $maxResults) {
            $urlParams['max-results'] = $maxResults;
        }

        $url = self::CONTACTS_API_URL . http_build_query($urlParams);

        try {
            $this->googleClient->setup();
            $googleAuthOAuth2 = $this->googleClient->getAuth();
            $request = $googleAuthOAuth2->authenticatedRequest(new Google_Http_Request($url));
        } catch (Google_Auth_Exception $e) {
            return null;
        }

        if ($request->getResponseHttpCode() === 200) {
            return $this->parse($request->getResponseBody());
        }

        return null;
    }

    private function parse($string)
    {
        $array = $this->addNode(simplexml_load_string($string));
        $contacts = array();
        foreach ($array["children"] as $item) {
            foreach ($item as $key => $element) {
                if ($element == "entry") {
                    $contact = array();
                    foreach ($item["children"] as $key => $data) {
                        switch ($data['name']) {
                            case "title":
                            {
                                if (array_key_exists('content', $data)) {
                                    $contact["name"] = $data['content'];
                                }
                                break;
                            }
                            case "email":
                            {
                                if (array_key_exists('attributes', $data) && array_key_exists(
                                        'address',
                                        $data['attributes']
                                    )
                                ) {
                                    $contact["email"] = $data['attributes']['address'];
                                }
                                break;
                            }
                        }
                    }

                    if (array_key_exists('email', $contact)) {
                        $contacts[$contact['email']] = $contact;
                    }
                }
            }
        }

        return $contacts;
    }

    private function addNode($node, &$parent = null, $namespace = '', $recursive = false)
    {
        $namespaces = $node->getNameSpaces(true);
        $content = "$node";

        $r['name'] = $node->getName();
        if (!$recursive) {
            $tmp = array_keys($node->getNameSpaces(false));
            $r['namespace'] = $tmp[0];
            $r['namespaces'] = $namespaces;
        }

        if ($namespace) {
            $r['namespace'] = $namespace;
        }

        if ($content) {
            $r['content'] = $content;
        }

        foreach ($namespaces as $pre => $ns) {
            foreach ($node->children($ns) as $k => $v) {
                $this->addNode($v, $r['children'], $pre, true);
            }

            foreach ($node->attributes($ns) as $k => $v) {
                $r['attributes'][$k] = "$pre:$v";
            }
        }

        foreach ($node->children() as $k => $v) {
            $this->addNode($v, $r['children'], '', true);
        }

        foreach ($node->attributes() as $k => $v) {
            $r['attributes'][$k] = "$v";
        }

        $parent[] = & $r;

        return $parent[0];
    }
}
