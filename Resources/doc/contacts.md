Contacts
============

Provide a service "bit_google.contacts" to short url with the google api

1. Accessing the service from controller:

        $this->get('bit_google.contacts')->getContacts();

this will return an array with the next format:

        [
           email-1@domain.com: [
                                email: "email-1@domain.com"
                                name: "contact name 1"
                             ],
           email-2@domain.com: [
                                email: "email-2@domain.com"
                                name: "contact name 2"
                             ],
                             ...,
           email-n@domain.com: [
                                email: "email-n@domain.com"
                                name: "contact name n"
                             ]
        ]