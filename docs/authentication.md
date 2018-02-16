Security Authenticators
=======================

_Based on Guard, many authenticators are available to set your authentication system._ 

### Available authenticators

- BasicAuthenticator: expect a basic authentication with valid username and password
- EmailAuthenticator: expect a valid email
- TokenAuthenticator: expect a valid token 

### User model

The User model must implement `Symfony\Component\Security\Core\User\UserInterface`

### User repository

The User repository must implement `Biig\Component\User\Security\User\UserTokenProviderInterface`

### Services definition

Define the authenticators you need in your services.yml file.
 
```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    Biig\Component\User\Security\Authenticator\BasicAuthenticator: ~
    Biig\Component\User\Security\Authenticator\EmailAuthenticator: ~
    Biig\Component\User\Security\Authenticator\TokenAuthenticator:
        $userRepository: '@App\Repository\UserRepository'

```

### Configure the authenticators

Here is an example of configuration.
 
```yaml
security:
    providers:
        user_provider:
            entity:
                class: App\Model\User
                property: email
    firewalls:
        login:
            pattern: ^/login
            anonymous: ~
            provider: user_provider
            guard:
                authenticators:
                    - Biig\Component\User\Security\Authenticator\BasicAuthenticator

        reset_password:
            pattern: ^/reset-password
            anonymous: ~
            provider: user_provider
            guard:
                authenticators:
                    - Biig\Component\User\Security\Authenticator\TokenAuthenticator

        forget_password:
            pattern: ^/forget-password
            anonymous: ~
            provider: user_provider
            guard:
                authenticators:
                    - Biig\Component\User\Security\Authenticator\EmailAuthenticator
```
