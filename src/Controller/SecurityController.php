<?php

namespace App\Controller;

use App\Service\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(name: 'auth.')]
class SecurityController extends AbstractController
{
    public function __construct(private SecurityService $securityService)
    {
    }

    #[Route('/login', name: 'login')]
    public function login(): Response
    {
        return $this->securityService->login();
    }

    #[Route('/register', name: 'register')]
    public function register(): Response
    {
        return $this->securityService->register();
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
