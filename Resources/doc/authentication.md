Authentication
============
1. Add this configuration to use the `security component`:

        # application/config/config.yml
            security:
                firewalls:
                    secured_area:
                        pattern:   ^/.*
                        bit_google:
                            provider: google
                            default_target_path: /
                            always_use_default_target_path: true
                            check_path: /google_login_check
                            failure_path: /
                            failure_forward: true
                        anonymous: true
                        logout: true

              access_control:
                  - { path: ^/.*, role: [IS_AUTHENTICATED_ANONYMOUSLY] }

2. Optionally define a custom user provider class and use it as the provider or define path for login

        # application/config/config.yml
            security:
                providers:
                    # choose the provider name freely
                    google:
                        id: google.user

3. Optionally use access control to secure specific URLs

        # application/config/config.yml
            security:
              # ...

              access_control:
                  - { path: ^/google/,           role: [ROLE_GOOGLE] }
                  - { path: ^/.*,                role: [IS_AUTHENTICATED_ANONYMOUSLY] }

4. Include the login button in your templates

Just add the following code in one of your templates:

        <a href="{{ google_login_url() }}">Google Login</a>

This will get you the login url

5. Example Customer User Provider using the FOS\UserBundle

This requires adding a service for the custom user provider which is then set to the provider id, in the "provider"
section of the config.yml:

        services:
            google.user:
                class: class: Acme\MyBundle\Security\User\Provider\GoogleProvider
                arguments:
                      googleUser: @bit_google.user
                      userManager: @fos_user.user_manager

        #Acme\MyBundle\Security\User\Provider\GoogleProvider
            <?php
            Acme\MyBundle\Security\User\Provider;

            use BIT\GoogleBundle\Google\GoogleUser;
            use FOS\UserBundle\Model\UserManager;

            class GoogleProvider implements UserProviderInterface
            {
                protected $userManager;

                protected $providerName;

                protected $googleUser;

                public function __construct(GoogleUser $googleUser, UserManager $userManager)
                {
                    $this->googleUser = $googleUser;
                    $this->userManager = $userManager;
                    $this->providerName = "Google";
                }

                protected function getData()
                {
                    $data = array();

                    try{
                        $googlePlusPerson = $this->googleUser->getInfo();
                    }catch(\Exception $e){
                        return $data;
                    }

                    $data ['id'] = $googlePlusPerson->getId();
                    $data ['displayName'] = $googlePlusPerson->getDisplayName();
                    $data ['lastname'] = "";
                    $data ['lastname2'] = "";
                    $data ['photo'] = $googlePlusPerson->getImage();

                    $email = $googlePlusPerson->getEmails()[0]->getValue();

                    $data ['email'] = $email;
                    $data ['username'] = $email;

                    return $data;
                }

                public function supportsClass($class)
                {
                    return $class === $this->getClass();
                }

                public function findUserByEmail($email)
                {
                    try{
                        return $this->userManager->findUserByUsernameOrEmail($email);
                    }catch(NoResultException $e){
                        return null;
                    }
                }

                public function loadUserByUsername($id)
                {
                    $account = $this->getUser($id);

                    if (empty ($account)) {
                        throw new UsernameNotFoundException (sprintf('The user is not authenticated on ', $this->providerName));
                    }

                    return $account;
                }

                private function getUser($id)
                {
                    $account = null;
                    $data = $this->getData();

                    if (!empty($data) && array_key_exists('email', $data)) {
                        $account = $this->userManager->findUserByEmail($data['email']);

                        if (empty ($account)) {
                            $data['password'] = uniqid("ramdom", true);
                            $account = $this->userManager->createUser();
                            $account->setEmail($data['email']);
                            $account->setUsername($data['email']);
                            $account->setPassword('');
                            $this->userManager->updateUser($account);
                        }

                        return $account;
                    }
                }

            }

Finally also needs to add a getGoogleId() method to the User model. The following example also adds "firstname" and
"lastname" properties:

        #Acme\MyBundle\Entity\User
            <?php
            Acme\MyBundle\Entity;
            use Doctrine\Common\Collections\ArrayCollection;
            use FOS\UserBundle\Entity\User as BaseUser;
            use Doctrine\ORM\Mapping as ORM;

            /**
             * @ORM\Entity
             * @ORM\Table(name="system_user")
             */
            class User extends BaseUser
            {
              /**
               * @ORM\Id
               * @ORM\Column(type="integer")
               * @ORM\generatedValue(strategy="AUTO")
               */
              protected $id;

              /**
               * @ORM\Column(type="string", length=40, nullable=true)
               */
              protected $googleID;

              /**
               * @ORM\Column(type="string", length=100, nullable=true)
               */
              protected $firstname;

              /**
               * @ORM\Column(type="string", length=100, nullable=true)
               */
              protected $lastname;

              /**
               * @ORM\Column(type="string", length=100, nullable=true)
               */
              protected $lastname2;

              public function __construct( )
              {
                parent::__construct( );
              }

              public function getId( )
              {
                return $this->id;
              }

              public function getFirstName( )
              {
                return $this->firstname;
              }

              public function setFirstName( $firstname )
              {
                $this->firstname = $firstname;
              }

              public function getLastName( )
              {
                return $this->lastname;
              }

              public function setLastName( $lastname )
              {
                $this->lastname = $lastname;
              }

              public function getLastName2( )
              {
                return $this->lastname2;
              }

              public function setLastName2( $lastname2 )
              {
                $this->lastname2 = $lastname2;
              }

              public function getFullName( )
              {
                $fullName = ( $this->getFirstName( ) ) ? $this->getFirstName( ) . ' ' : '';
                $fullName .= ( $this->getLastName( ) ) ? $this->getLastName( ) . ' ' : '';
                $fullName .= ( $this->getLastName2( ) ) ? $this->getLastName2( ) . ' ' : '';
                return $fullName;
              }

              public function setGoogleID( $googleID )
              {
                $this->googleID = $googleID;
              }

              public function getGoogleID( )
              {
                return $this->googleID;
              }


              public function setSalt( $salt )
              {
                $this->salt = $salt;
              }
            }
