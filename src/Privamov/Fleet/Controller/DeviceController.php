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
use Privamov\Fleet\Entity\Lending;
use Privamov\Fleet\Entity\LendingManager;
use Privamov\Fleet\Form\DeviceForm;
use Privamov\Fleet\Form\NewLendingForm;
use Privamov\Librarian\Librarian;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeviceController
{
    private $deviceTypeManager;
    private $deviceManager;
    private $lendingManager;
    private $librarian;
    private $formFactory;
    private $urlGenerator;
    private $twig;

    public function __construct(DeviceTypeManager $deviceTypeManager, DeviceManager $deviceManager, LendingManager $lendingManager, Librarian $librarian, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, \Twig_Environment $twig)
    {
        $this->deviceTypeManager = $deviceTypeManager;
        $this->deviceManager = $deviceManager;
        $this->lendingManager = $lendingManager;
        $this->librarian = $librarian;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function create(Request $request)
    {
        /** @var Device $device */
        $device = $this->deviceManager->newEntity();
        $form = $this->formFactory->create(new DeviceForm($this->deviceTypeManager->find()), $device);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->deviceManager->store($device);
            return new RedirectResponse($this->urlGenerator->generate('device_index'));
        }
        return $this->twig->render('device/new.html.twig', array('form' => $form->createView()));
    }

    public function edit($id, Request $request)
    {
        /** @var Device $device */
        $device = $this->deviceManager->findById($id);
        if (!$device) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(new DeviceForm($this->deviceTypeManager->find()), $device);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->deviceManager->store($device);
            return new RedirectResponse($this->urlGenerator->generate('device_show', ['id' => $device->getId()]));
        }
        return $this->twig->render('device/edit.html.twig', [
            'form' => $form->createView(),
            'device' => $device,
        ]);
    }

    public function index(Request $request)
    {
        return $this->twig->render('device/index.html.twig', [
            'devices' => $this->deviceManager->find(),
            'types' => $this->deviceTypeManager->find(),
        ]);
    }

    public function show($id, Request $request)
    {
        /** @var Device $device */
        $device = $this->deviceManager->findById($id);
        if (!$device) {
            throw new NotFoundHttpException();
        }

        /** @var Lending $lending */
        $lending = $this->lendingManager->newEntity(['device' => $device->getId()]);
        $form = $this->formFactory->create(new NewLendingForm(), $lending);

        $lendings = $this->lendingManager->findByDevice($device->getId());
        $lastLending = count($lendings) ? $lendings[array_keys($lendings)[0]] : null;

        return $this->twig->render('device/show.html.twig', [
            'search' => $request->query->get('q'), // If we came from a query
            'device' => $device,
            'type' => $this->deviceTypeManager->findById($device->type),
            'lendings' => $this->lendingManager->findByDevice($device->getId()),
            'lastLending' => $lastLending,
            'form' => $form->createView(),
            'spotme_url' => $this->librarian->getString('service/spotme/ui'),
        ]);
    }
}
