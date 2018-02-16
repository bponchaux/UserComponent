JWT
===

_The command `biig:jwt:generate` is available to generate a JWT token for a user (dev environment)._ 

### User model

The User model must implement `Symfony\Component\Security\Core\User\UserInterface`

### User repository

The User repository must implement `Biig\Component\User\Persistence\RepositoryInterface`

### Services definition

Define the authenticators you need in your services_dev.yml file.
 
```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true
        public: false

    Biig\Component\User\Jwt\JwtGeneratorInterface:
        alias: App\Jwt\JwtGeneratorToken
    
    Biig\Component\User\Persistence\RepositoryInterface:
        alias: App\Repository\UserRepository

```
