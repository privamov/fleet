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

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', 'dashboard.controller:index')
    ->bind('homepage');

$app->match('/login', 'auth.controller:login')
    ->method('POST|GET')
    ->bind('login');

$app->get('/logout', 'auth.controller:logout')
    ->bind('logout');

$app->get('/search', 'search.controller:index')
    ->bind('search');

$app->get('/type', 'devicetype.controller:index')
    ->bind('type_index');

$app->match('/type/new', 'devicetype.controller:create')
    ->method('POST|GET')
    ->bind('type_new');

$app->match('/type/edit/{id}', 'devicetype.controller:edit')
    ->method('POST|GET')
    ->bind('type_edit');

$app->get('/type/show/{id}', 'devicetype.controller:show')
    ->bind('type_show');

$app->get('/device', 'device.controller:index')
    ->bind('device_index');

$app->match('/device/show/{id}', 'device.controller:show')
    ->method('POST|GET')
    ->bind('device_show');

$app->match('/device/new', 'device.controller:create')
    ->method('POST|GET')
    ->bind('device_new');

$app->match('/device/edit/{id}', 'device.controller:edit')
    ->method('POST|GET')
    ->bind('device_edit');

$app->get('/lending/back/{id}', 'lending.controller:back')
    ->bind('lending_back');

$app->match('/lending/edit/{id}', 'lending.controller:edit')
    ->method('POST|GET')
    ->bind('lending_edit');

$app->match('/lending/new/{id}', 'lending.controller:create')
    ->method('POST|GET')
    ->bind('lending_new');

$app->match('/lending/delete/{id}', 'lending.controller:delete')
    ->method('POST|GET')
    ->bind('lending_delete');

$app->get('/api/lending/{token}', 'lending.controller:apiShow');

$app->get('/_cc', function () use ($app) {
    $fs = new Filesystem();
    $fs->remove(__DIR__ . '/../var/cache');

    return $app->redirect($app['url_generator']->generate('homepage'));
});

$app->before(function (Request $request) use ($app) {
    $session = $request->getSession();
    if (!$session->has('fleet.login') && false === strpos($request->getRequestUri(), '/api') && $request->attributes->get('_route') !== 'login') {
        return $app->redirect($app['url_generator']->generate('login', ['next' => $request->getRequestUri()]));
    }
});

$app->error(function (\Exception $e, Request $request, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/' . $code . '.html',
        'errors/' . substr($code, 0, 2) . 'x.html',
        'errors/' . substr($code, 0, 1) . 'xx.html',
        'errors/default.html',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
