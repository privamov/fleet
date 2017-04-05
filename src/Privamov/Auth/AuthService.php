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

namespace Privamov\Auth;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentication service dealing with multiple authentication handlers.
 *
 * @author Vincent Primault <vincent.primault@liris.cnrs.fr>
 */
class AuthService
{
    /**
     * @var AuthHandler[]
     */
    private $handlers = [];

    /**
     * @var string
     */
    private $prefix;

    /**
     * @param AuthHandler[] $handlers
     * @param string $prefix
     */
    public function __construct($handlers, $prefix)
    {
        foreach ($handlers as $handler) {
            $this->handlers[$handler->getDomain()] = $handler;
        }
        $this->prefix = $prefix;
    }

    /**
     * Return available domains.
     *
     * @return array
     */
    public function getDomains()
    {
        return array_keys($this->handlers);
    }

    public function preAuthenticate(Request $request)
    {
        $partialToken = [];
        foreach (['login', 'domain'] as $key) {
            if ($request->cookies->has($this->prefix . $key)) {
                $partialToken[$key] = $request->cookies->get($this->prefix . $key);
            }
        }
        return $partialToken;
    }

    /**
     * Try to authenticate a user.
     *
     * @param string $domain Domain where to authenticate user
     * @param string $login A login
     * @param string $password A plain password
     * @return array|false False if authentication failed, a token otherwise (must include a "username" key)
     * @throws AuthException If some internal error occured during the process, other than wrong credentials
     */
    public function authenticate($domain, $login, $password)
    {
        if (!isset($this->handlers[$domain])) {
            throw new \InvalidArgumentException('Unknown domain "' . $domain . '".');
        }

        return $this->handlers[$domain]->authenticate($login, $password);
    }

    public function postAuthenticate(array $token, Request $request, Response $response)
    {
        // Remember login and domain for next time.
        $response->headers->setCookie(new Cookie($this->prefix . 'login', $token['login']));
        $response->headers->setCookie(new Cookie($this->prefix . 'domain', $token['domain']));

        // Store token in session.
        $session = $request->getSession();
        foreach ($token as $key => $value) {
            $session->set($this->prefix . $key, $value);
        }
        $session->save();
    }

    public function logout(Request $request, Response $response)
    {
        $request->getSession()->invalidate();
    }
}
