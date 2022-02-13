<?php

namespace App\Service;

use Exception;
use LogicException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment as Twig;

class UtilsService
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private Twig $twig,
        private UrlGeneratorInterface $router,
        private RequestStack $requestStack,
        private FormFactoryInterface $formFactory,
    ) {
    }

    /**
     * Returns the current logged in user.
     */
    public function getUser(): UserInterface|null
    {
        $token = $this->tokenStorage->getToken();

        return $token ? $token->getUser() : null;
    }

    /**
     * Returns the current request.
     */
    public function getRequest(): Request|null
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * Renders a twig template & wraps it in an http Response.
     *
     * @param string              $view       The twig template to render
     * @param array<string,mixed> $parameters The variables to pass to twig
     * @param Response            $response   The response object to return. Pass none to create a new one.
     *
     * @return Response
     */
    public function render(string $view, array $parameters = [], Response $response = null): Response
    {
        if (null === $response) {
            $response = new Response();
        }
        $response->setContent($this->twig->render($view, $parameters));

        return $response;
    }

    /**
     * Returns a redirect response towards the specified url.
     *
     * @param string $url    the url to redirect to
     * @param int    $status the http status of the response
     */
    public function redirect(string $url, int $status = 302): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * Returns a new redirect response and generates the url with the given route.
     *
     * @param string   $route      the route to redirect to
     * @param string[] $parameters the url context parameters
     * @param int      $status     the http status of the response
     */
    public function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse
    {
        return $this->redirect($this->router->generate($route, $parameters), $status);
    }

    /**
     * Returns a new FormInterface with the given form.
     *
     * @param string   $type    the formType to generate
     * @param mixed    $data    the base data to update
     * @param string[] $options the options to pass to the form generator
     */
    public function createForm(
        string $type,
        mixed $data = null,
        array $options = []
    ): FormInterface {
        return $this->formFactory->create($type, $data, $options);
    }
}
