<?php
/*
 * Fleet is a program whose purpose is to manage a fleet of mobile devices.
 * Copyright (C) 2016-2017 Vincent Primault <vincent.primault@liris.cnrs.fr>
 *
 * Fleet is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Fleet is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Fleet.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Privamov\Fleet\Controller;

use Privamov\Auth\AuthException;
use Privamov\Auth\AuthService;
use Privamov\Auth\Form\LoginForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AuthController
{
    /**
     * @var AuthService
     */
    private $authService;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * Constructor.
     *
     * @param AuthService $authService Authentication manager
     * @param FormFactoryInterface $formFactory A form factory
     * @param UrlGeneratorInterface $urlGenerator An URL generator
     * @param \Twig_Environment $twig Twig templating engine
     */
    public function __construct(AuthService $authService, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, \Twig_Environment $twig)
    {
        $this->authService = $authService;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    /**
     * Ask a user to log in.
     *
     * @param Request $request
     * @return string|RedirectResponse
     */
    public function login(Request $request)
    {
        $session = $request->getSession();
        if ($session->has('fleet.login')) {
            return new RedirectResponse($this->urlGenerator->generate('homepage'));
        }

        $data = $this->authService->preAuthenticate($request);
        $form = $this->formFactory->create(new LoginForm($this->authService->getDomains()), $data);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            try {
                $token = $this->authService->authenticate($data['domain'], $data['login'], $data['password']);
            } catch (AuthException $ex) {
                $session->getFlashBag()->add('error', 'An internal error occured: ' . $ex->getMessage());

                return new RedirectResponse($this->urlGenerator->generate('login'));
            }

            if (false === $token) {
                $session->getFlashBag()->add('error', 'Unknown credentials.');

                return new RedirectResponse($this->urlGenerator->generate('login'));
            } else {
                // Generate redirection response.
                if ($request->query->has('next') && $request->query->has('next') !== $this->urlGenerator->generate('login')) {
                    $target = $request->query->has('next');
                } else {
                    $target = $this->urlGenerator->generate('homepage');
                }
                $response = new RedirectResponse($target);

                $token['login'] = $data['login'];
                $token['domain'] = $data['domain'];
                $this->authService->postAuthenticate($token, $request, $response);

                return $response;
            }
        }

        return $this->twig->render('auth/login.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * Log out a user.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request)
    {
        $response = new RedirectResponse($this->urlGenerator->generate('login'));
        $this->authService->logout($request, $response);

        return $response;
    }
}
