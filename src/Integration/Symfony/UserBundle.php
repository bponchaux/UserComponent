<?php

namespace Biig\Component\User\Integration\Symfony;

use Biig\Component\User\Integration\Symfony\DependencyInjection\UserExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UserBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        return new UserExtension();
    }
}
