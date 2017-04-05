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

// Configure Twig.
$app['twig.path'] = array(__DIR__ . '/../templates');
$app['twig.options'] = array('cache' => __DIR__ . '/../var/cache/twig');
$app['twig.form.templates'] = array('form/bootstrap_3_horizontal_layout.html.twig');

// Configure authentication.
$app['auth.handlers'] = [
    new \Privamov\Auth\LocalAuthHandler(['admin' => 'pass']),
];

$app['librarian_path'] = getenv('LIBRARIAN_PATH');

// Configure production logging with Monolog.
$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../var/logs/silex_prod.log',
    'monolog.level' => 'ERROR',
));
