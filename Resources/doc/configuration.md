Configuration
============
1. Add the following routes to your application and point them at actual controller actions

        #application/config/routing.yml
            _security_check:
              pattern:  /login_check
            _security_logout:
              pattern:  /logout

        #application/config/routing.xml
            <route id="_security_check" pattern="/login_check" />
            <route id="_security_logout" pattern="/logout" />

2. Configure the `google` service in your config:

        # application/config/config.yml
            bit_google:
                app_name: appName
                client_id: clientid.apps.googleusercontent.com
                client_secret: secret
                state: auth
                access_type: auto
                scopes:
                    profile: true
                    email: true
                    contact: true
                callback_route: google_user_security
