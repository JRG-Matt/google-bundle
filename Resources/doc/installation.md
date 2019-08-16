Installation
============

1. Add this bundle to the composer file:

        #composer.json
            {
            "require": {
                "bitgandtter/google-bundle": ">=1.0"
                }
            }

2. Run the composer to download the bundle

        $ composer update

3. Add this bundle to your application's kernel:

        //app/AppKernel.php
            public function registerBundles()
            {
              return array(
                  // ...
                  new BIT\GoogleBundle\BITGoogleBundle(),
                  // ...
              );
            }
