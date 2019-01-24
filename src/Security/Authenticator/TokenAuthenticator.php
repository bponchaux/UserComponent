<?php

namespace Biig\Component\User\Security\Authenticator;

use Biig\Component\User\Security\User\UserTokenProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\PreconditionFailedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    /**
     * @var UserTokenProviderInterface
     */
    private $userRepository;

    public function __construct(UserTokenProviderInterface $userRepository)
    {
        if (!$userRepository instanceof UserTokenProviderInterface) {
            throw new \InvalidArgumentException(sprintf('The entity repository "%s" must implement "Biig\Component\User\Security\User\UserTokenProviderInterface".', get_class($userRepository)));
        }

        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getCredentials(Request $request)
    {
        if (!$request->attributes->has('token')) {
            throw new PreconditionFailedHttpException('Token parameter is missing');
        }

        return ['token' => $request->attributes->get('token')];
    }

    /**
     * {@inheritdoc}
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $this->userRepository->loadUserByConfirmationToken($credentials['token']);
    }

    /**
     * {@inheritdoc}
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        throw new AccessDeniedHttpException('Bad token');
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }

    /**
     *{@inheritdoc}
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
            throw new UnauthorizedHttpException('Token', 'Bad credentials.', $exception);
        }

        throw $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(Request $request)
    {
        return 1 === \preg_match('/reset-password/', $request->getPathInfo()) && $this->isAllowedRequest($request);
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
                Request::METHOD_PATCH,
                Request::METHOD_PUT,
            ]
        );
    }
}
