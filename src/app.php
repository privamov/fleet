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

use Privamov\Auth\AuthServiceProvider;
use Privamov\Fleet\Controller\AuthController;
use Privamov\Fleet\Controller\DashboardController;
use Privamov\Fleet\Controller\DeviceController;
use Privamov\Fleet\Controller\DeviceTypeController;
use Privamov\Fleet\Controller\LendingController;
use Privamov\Fleet\Controller\SearchController;
use Privamov\Fleet\Entity\DeviceManager;
use Privamov\Fleet\Entity\DeviceTypeManager;
use Privamov\Fleet\Entity\LendingManager;
use Privamov\Fleet\SearchService;
use Privamov\Librarian\Librarian;
use Privamov\Librarian\LibrarianServiceProvider;
use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\RoutingServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\HttpFoundation\Request;

$app = new Application();
$app->register(new RoutingServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new LocaleServiceProvider());
$app->register(new TranslationServiceProvider());
$app->register(new LibrarianServiceProvider());
$app->register(new AuthServiceProvider('fleet.'));

// Register controllers.
$app['device.controller'] = function () use ($app) {
    return new DeviceController($app['devicetype.manager'], $app['device.manager'], $app['lending.manager'], $app['librarian'], $app['form.factory'], $app['url_generator'], $app['twig']);
};
$app['devicetype.controller'] = function () use ($app) {
    return new DeviceTypeController($app['devicetype.manager'], $app['device.manager'], $app['form.factory'], $app['url_generator'], $app['twig']);
};
$app['lending.controller'] = function () use ($app) {
    return new LendingController($app['device.manager'], $app['lending.manager'], $app['form.factory'], $app['url_generator'], $app['twig']);
};
$app['dashboard.controller'] = function () use ($app) {
    return new DashboardController($app['devicetype.manager'], $app['device.manager'], $app['lending.manager'], $app['twig']);
};
$app['auth.controller'] = function () use ($app) {
    return new AuthController($app['auth_service'], $app['form.factory'], $app['url_generator'], $app['twig']);
};
$app['search.controller'] = function () use ($app) {
    return new SearchController($app['devicetype.manager'], $app['search_service'], $app['url_generator'], $app['twig']);
};

// Register DAO services.
$app['devicetype.manager'] = function ($app) {
    return new DeviceTypeManager($app['db']);
};
$app['device.manager'] = function ($app) {
    return new DeviceManager($app['db']);
};
$app['lending.manager'] = function ($app) {
    return new LendingManager($app['db']);
};

// Register other services.
$app['search_service'] = function ($app) {
    return new SearchService($app['device.manager'], $app['lending.manager']);
};

// Register database connection.
$app['db'] = function ($app) {
    /** @var Librarian $librarian */
    $librarian = $app['librarian'];
    $driver = $librarian->getString('config/db/fleet/driver', 'mysql');

    return new \medoo([
        'database_type' => $driver,
        'database_name' => $librarian->getString('config/db/fleet/database', 'fleet'),
        'server' => $librarian->getString('config/db/fleet/host', 'localhost'),
        'socket' => $librarian->getString('config/db/fleet/socket'),
        'username' => $librarian->getString('config/db/fleet/user', ($driver === 'pgsql') ? 'postgres' : 'root'),
        'password' => $librarian->getString('config/db/fleet/password', ''),
        'charset' => 'utf8',
    ]);
};

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...

    $twig->addFunction(new \Twig_SimpleFunction('asset', function ($asset) use ($app) {
        return $app['request_stack']->getMasterRequest()->getBasepath() . '/' . $asset;
    }));

    return $twig;
});

$app->before(function (Request $request) use ($app) {
    $app['twig']->addGlobal('current_route', $request->attributes->get('_route'));
    $app['twig']->addGlobal('request', $request);
});


return $app;
