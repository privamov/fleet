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

use Privamov\Fleet\Entity\Device;
use Privamov\Fleet\Entity\DeviceManager;
use Privamov\Fleet\Entity\DeviceTypeManager;
use Privamov\Fleet\Entity\LendingManager;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DashboardController
{
    private $deviceTypeManager;
    private $deviceManager;
    private $lendingManager;
    private $twig;

    public function __construct(DeviceTypeManager $deviceTypeManager, DeviceManager $deviceManager, LendingManager $lendingManager, \Twig_Environment $twig)
    {
        $this->deviceTypeManager = $deviceTypeManager;
        $this->deviceManager = $deviceManager;
        $this->lendingManager = $lendingManager;
        $this->twig = $twig;
    }

    public function index()
    {
        $byTypeSegment = $this->lendingManager->countByTypeAndSegment();
        $segments = array_keys($byTypeSegment['total']);
        $byStatus = $this->deviceManager->countByStatus();

        return $this->twig->render('dashboard/index.html.twig', [
            'types' => $this->deviceTypeManager->find(),
            'byTypeSegment' => $byTypeSegment,
            'segments' => $segments,
            'statuses' => Device::$statuses,
            'byTypeStatus' => $this->lendingManager->countByTypeAndStatus(),
            'byStatus' => $byStatus,
            'total' => array_sum($byStatus),
        ]);
    }
}
