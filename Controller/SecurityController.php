<?php

namespace HitcKit\AuthBundle\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * @Route(path="{zero}", defaults={"zero": ""}, name="hitckit_auth_login_site")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function siteLogin(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@HitcKitAuth/login_site.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route(path="{zero}", defaults={"zero": ""}, name="hitckit_auth_login_admin")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function adminLogin(AuthenticationUtils $authenticationUtils): Response
    {
        if (!isset($this->getParameter('kernel.bundles')['SonataAdminBundle'])) {
            throw $this->createNotFoundException();
        }

        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('@HitcKitAuth/login_admin.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route(path="{zero}", defaults={"zero": ""}, name="hitckit_auth_logout")
     */
    public function siteLogout()
    {
        $this->redirect('/');
    }
}
