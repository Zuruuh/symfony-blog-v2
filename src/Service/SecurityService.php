<?php

namespace App\Service;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\SessionAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityService
{
    public function __construct(
        private UtilsService $utils,
        private AuthenticationUtils $authenticationUtils,
        private UserPasswordHasherInterface $hasher,
        private EntityManagerInterface $em,
        private UserAuthenticatorInterface $userAuthenticator,
        private SessionAuthenticator $authenticator,
    ) {
    }

    public function login(): Response
    {
        if ($this->utils->getUser()) {
            return $this->utils->redirectToRoute('home');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->utils->render('auth/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    public function register(): Response
    {
        $user = new User();
        $request = $this->utils->getRequest();
        $form = $this->utils->createForm(RegistrationFormType::class, $user);
        if ($request !== null) {
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $plainPassword = $form->get('password')->getData();
                if (gettype($plainPassword) !== 'string') {
                    throw new BadRequestException('Password cannot be blank');
                }
                $user->setPassword(
                    $this->hasher->hashPassword(
                        $user,
                        strval($plainPassword)
                    )
                );
                dump($user->getPassword());

                $this->em->persist($user);
                $this->em->flush();
                // do anything else you need here, like send an email

                $response = $this->userAuthenticator->authenticateUser(
                    $user,
                    $this->authenticator,
                    $request
                );

                return $response ?? new Response('Could not sign in user :(');
            }
        }

        return $this->utils->render('auth/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
