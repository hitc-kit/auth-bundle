<?php

namespace HitcKit\AuthBundle\Security;

use Exception;
use HitcKit\AuthBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    public const ROUTE_LOGIN_SITE = 'hitc_kit_auth_login_site';
    public const ROUTE_LOGIN_ADMIN = 'hitc_kit_auth_login_admin';
    public const ROUTE_HOME_SITE = 'home';
    public const ROUTE_HOME_ADMIN = 'hitc_kit_admin_dashboard';

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $request;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, RequestStack $requestStack, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->request = $requestStack->getCurrentRequest();
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        $routes = [self::ROUTE_LOGIN_SITE, self::ROUTE_LOGIN_ADMIN];

        return in_array($request->attributes->get('_route'), $routes)
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];

        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);

        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $credentials['email']]);

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('email_not_found');
        }

        return $user;
    }

    /**
     * @param mixed $credentials
     * @param UserInterface $user
     * @return bool|void
     * @throws Exception
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    /**
     * @param Request $request
     * @param TokenInterface $token
     * @param mixed $providerKey
     * @return RedirectResponse|Response|null
     * @throws Exception
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }

        $route = $request->attributes->get('_route') === self::ROUTE_LOGIN_ADMIN
            ? self::ROUTE_HOME_ADMIN
            : self::ROUTE_HOME_SITE
        ;

        return new RedirectResponse($this->urlGenerator->generate($route));
    }

    protected function getLoginUrl()
    {
        $path = $this->request->getPathInfo();
        $pathAdmin = $this->urlGenerator->generate(self::ROUTE_HOME_ADMIN);
        $route = strpos($path, $pathAdmin) === 0 ? self::ROUTE_LOGIN_ADMIN : self::ROUTE_LOGIN_SITE;
        return $this->urlGenerator->generate($route);
    }
}
