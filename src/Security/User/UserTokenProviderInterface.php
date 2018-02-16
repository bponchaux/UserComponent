<?php

namespace Biig\Component\User\Security\User;

interface UserTokenProviderInterface
{
    /**
     * @param $token
     *
     * @return User\null
     */
    public function loadUserByConfirmationToken($token);
}
