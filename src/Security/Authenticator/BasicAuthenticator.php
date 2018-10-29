<?php

namespace Biig\Component\User\Security\Authenticator;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class BasicAuthenticator.
 * @deprecated You should use the standard way of Symfony to do it. Learn more here:
 *             https://symfony.com/doc/current/security/auth_providers.html#security-http-basic
 */
class BasicAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var EncoderFactoryInterface
     */
    protected $encoders;

    public function __construct(EncoderFactoryInterface $encoders)
    {
        $this->encoders = $encoders;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        if (null === $authorization = $request->headers->get('Authorization')) {
            throw new UnauthorizedHttpException('Basic', 'Header Authorization not found');
        }

        $matches = [];
        if (1 !== preg_match('/^Basic (.+)$/', $authorization, $matches)) {
            throw new UnauthorizedHttpException('Basic', 'Header Basic Authorization not found');
        }

        list($authorization, $realm) = $matches;

        $realm = \base64_decode($realm, true);
        $delimiterPos = \strpos($realm, ':');

        if (false === $delimiterPos) {
            throw new UnauthorizedHttpException('Basic', 'Base64 decode error. Require 2 parameters');
        }

        return [
            'username' => \substr($realm, 0, $delimiterPos),
            'password' => \substr($realm, $delimiterPos + 1),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials['username']);
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        $encoder = $this->encoders->getEncoder($user);

        return $encoder->isPasswordValid($user->getPassword(), $credentials['password'], $user->getSalt());
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new UnauthorizedHttpException('Basic', 'Bad credentials');
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $exception = null)
    {
        if (null === $exception) {
            return;
        }

        if ($exception instanceof AuthenticationCredentialsNotFoundException) {
            throw new UnauthorizedHttpException('Basic', 'Bad credentials.', $exception);
        }

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return 1 === \preg_match('/login$/', $request->getPathInfo()) && $this->isAllowedRequest($request);
    }

    /**
     * @param Request $request
     *
     * @return bool
     */
    private function isAllowedRequest(Request $request)
    {
        return \in_array(
            $request->getMethod(),
            [
                Request::METHOD_GET,
            ]
        );
    }
}
