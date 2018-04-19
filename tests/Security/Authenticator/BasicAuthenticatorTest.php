<?php

namespace Biig\Component\User\Tests\Security\Authenticator;

use Biig\Component\User\Security\Authenticator\BasicAuthenticator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class BasicAuthenticatorTest extends TestCase
{
    /**
     * @var EncoderFactoryInterface
     */
    private $encoders;

    protected function setUp()
    {
        $this->encoders = $this->prophesize(EncoderFactoryInterface::class);
        $this->authenticator = new BasicAuthenticator($this->encoders->reveal());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function testGetCredentialsFailedWithoutHeaders()
    {
        $request = new Request([], [], [], [], [], [], null);
        $credentials = $this->authenticator->getCredentials($request);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException
     */
    public function testGetCredentialsFailedWithBadHeaders()
    {
        $server = ['HTTP_AUTHORIZATION' => 'Bad authorization'];
        $request = new Request([], [], [], [], [], $server, null);
        $credentials = $this->authenticator->getCredentials($request);
    }

    public function testGetCredentialsSuccess()
    {
        $server = ['HTTP_AUTHORIZATION' => 'Basic ' . \base64_encode('foo:bar')];
        $request = new Request([], [], [], [], [], $server, null);
        $credentials = $this->authenticator->getCredentials($request);

        $this->assertSame($credentials, [
            'username' => 'foo',
            'password' => 'bar',
        ]);
    }

    public function testGetUser()
    {
        $credentials = [
            'username' => 'foo',
            'password' => 'bar',
        ];

        $userProvider = $this->prophesize(UserProviderInterface::class);
        $userProvider->loadUserByUsername('foo')->shouldBeCalled();

        $this->authenticator->getUser($credentials, $userProvider->reveal());
    }

    public function testCheckCredentials()
    {
        $credentials = [
            'username' => 'foo',
            'password' => 'bar',
        ];

        $user = $this->prophesize(UserInterface::class);
        $user->getPassword()->shouldBeCalled()->willReturn('bar');
        $user->getSalt()->shouldBeCalled()->willReturn('salt');
        $passwordEncoder = $this->prophesize(PasswordEncoderInterface::class);
        $this->encoders->getEncoder($user)->shouldBeCalled()->willReturn($passwordEncoder);
        $passwordEncoder->isPasswordValid('bar', 'bar', 'salt')->shouldBeCalled();

        $this->authenticator->checkCredentials($credentials, $user->reveal());
    }

    public function testOnAuthenticationFailure()
    {
        $request = new Request([], [], [], [], [], [], null);

        $this->expectException(UnauthorizedHttpException::class);

        $exception = $this->prophesize(AuthenticationException::class);
        $this->authenticator->onAuthenticationFailure($request, $exception->reveal());
    }

    public function testSupportsRememberMe()
    {
        $result = $this->authenticator->supportsRememberMe();
        $this->assertFalse($result);
    }

    public function testStart()
    {
        $request = new Request([], [], [], [], [], [], null);

        $result = $this->authenticator->start($request, null);
        $this->assertNull($result);

        $this->expectException(UnauthorizedHttpException::class);
        $exception = $this->prophesize(AuthenticationCredentialsNotFoundException::class);
        $this->authenticator->start($request, $exception->reveal());

        $this->expectException(AuthenticationException::class);
        $exception = $this->prophesize(AuthenticationException::class);
        $this->authenticator->start($request, $exception->reveal());
    }

    public function testSupports()
    {
        $server = ['REQUEST_URI' => 'login', 'REQUEST_METHOD' => 'GET'];
        $request = new Request([], [], [], [], [], $server, null);
        $result = $this->authenticator->supports($request);
        $this->assertTrue($result);

        $server = ['REQUEST_URI' => 'login', 'REQUEST_METHOD' => 'POST'];
        $request = new Request([], [], [], [], [], $server, null);
        $result = $this->authenticator->supports($request);
        $this->assertFalse($result);

        $server = ['REQUEST_URI' => 'bad-request', 'REQUEST_METHOD' => 'GET'];
        $request = new Request([], [], [], [], [], $server, null);
        $result = $this->authenticator->supports($request);
        $this->assertFalse($result);
    }
}
