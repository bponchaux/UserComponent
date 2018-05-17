<?php

namespace Biig\Component\User\Tests\Security;

use Biig\Component\User\Security\AuthenticatedUserProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AuthenticatedUserProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testItGetAUserFromAToken()
    {
        $provider = new AuthenticatedUserProvider(
            $this->getTokenStorage($this->prophesize(UserInterface::class)->reveal())
        );

        $this->assertInstanceOf(UserInterface::class, $provider->getUser());
    }

    public function testItReturnNullIfNoToken()
    {
        $provider = new AuthenticatedUserProvider(
            $this->prophesize(TokenStorageInterface::class)->reveal()
        );

        $this->assertEquals(null, $provider->getUser());
    }

    public function testItReturnNullIfNoUser()
    {
        $provider = new AuthenticatedUserProvider(
            $this->getTokenStorage(null)
        );

        $this->assertEquals(null, $provider->getUser());
    }

    public function testItReturnNullIfWrongUser()
    {
        $provider = new AuthenticatedUserProvider(
            $this->getTokenStorage($this->prophesize(UserInterface::class)->reveal())
        );

        $this->assertEquals(null, $provider->getUser(Foo::class));
    }

    private function getTokenStorage($user = null)
    {
        $token = $this->prophesize(TokenInterface::class);
        $token->getUser()->willReturn($user);

        $tokenStorage = $this->prophesize(TokenStorageInterface::class);
        $tokenStorage->getToken()->willReturn($token->reveal());

        return $tokenStorage->reveal();
    }
}

class Foo
{
}
