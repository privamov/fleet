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

/**
 * A handler for an authentication method.
 *
 * @author Vincent Primault <vincent.primault@liris.cnrs.fr>
 */
interface AuthHandler
{
    /**
     * Try to authenticate a user.
     *
     * @param string $login A login
     * @param string $password A plain password
     * @return array|false False if authentication failed, a token otherwise (must include a "username" key)
     * @throws AuthException If some internal error occured during the process, other than wrong credentials
     */
    function authenticate($login, $password);

    /**
     * Return the authentication domain.
     *
     * @return string
     */
    function getDomain();
}
