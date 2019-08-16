Url Shorter
============

Provide a service "bit_google.url_shortener" to short url with the google api

1. Accessing the service from controller:

        $this->get("bit_google.url_shortener")->short("http://google.com");

2. Accessing the service from template

        {{ google_short_url("http://google.com") }}
