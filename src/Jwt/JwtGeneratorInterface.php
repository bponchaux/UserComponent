<?php

namespace Biig\Component\User\Jwt;

use Symfony\Component\Security\Core\User\UserInterface;

interface JwtGeneratorInterface
{
    /**
     * @param UserInterface $user
     *
     * @return string
     */
    public function create(UserInterface $user);
}
