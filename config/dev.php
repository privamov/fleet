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

// include the prod configuration
require __DIR__ . '/prod.dist.php';

// enable the debug mode
$app['debug'] = true;

$app['librarian_path'] = getenv('LIBRARIAN_PATH') ?: is_file(__DIR__ . '/../../librarian.properties') ? __DIR__ . '/../../librarian.properties' : __DIR__ . '/../../librarian.dist.properties';

$app->register(new Silex\Provider\MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../var/logs/silex_dev.log',
));

$app->register(new Silex\Provider\WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => __DIR__ . '/../var/cache/profiler',
));
