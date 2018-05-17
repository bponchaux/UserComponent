<?php

namespace Biig\Component\User\Security;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatedUserProvider
{
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param string|null $kind if you want a special kind of user
     *
     * @return UserInterface|null
     */
    public function getUser(string $kind = null)
    {
        if (null === $token = $this->tokenStorage->getToken()) {
            return null;
        }

        $user = $token->getUser();

        if (null !== $kind && get_class($user) !== $kind) {
            return null;
        }

        return $user;
    }
}
