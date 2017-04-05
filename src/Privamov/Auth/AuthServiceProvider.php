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

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AuthServiceProvider implements ServiceProviderInterface
{
    private $prefix;

    public function __construct($prefix = '/')
    {
        $this->prefix = $prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function register(Container $app)
    {
        $app['auth_service'] = function ($app) {
            return new AuthService(isset($app['auth.handlers']) ? $app['auth.handlers'] : [], $this->prefix);
        };
    }
}
