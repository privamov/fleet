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

use Privamov\Fleet\Entity\DeviceManager;
use Privamov\Fleet\Entity\DeviceType;
use Privamov\Fleet\Entity\DeviceTypeManager;
use Privamov\Fleet\Form\DeviceTypeForm;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DeviceTypeController
{
    private $deviceTypeManager;
    private $deviceManager;
    private $formFactory;
    private $urlGenerator;
    private $twig;

    public function __construct(DeviceTypeManager $deviceTypeManager, DeviceManager $deviceManager, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, \Twig_Environment $twig)
    {
        $this->deviceTypeManager = $deviceTypeManager;
        $this->deviceManager = $deviceManager;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function index()
    {
        return $this->twig->render('devicetype/index.html.twig', [
            'types' => $this->deviceTypeManager->find(),
        ]);
    }

    public function create(Request $request)
    {
        $type = DeviceType::create();
        $form = $this->formFactory->create(new DeviceTypeForm(), $type);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->deviceTypeManager->store($type);
            return new RedirectResponse($this->urlGenerator->generate('type_index'));
        }
        return $this->twig->render('devicetype/new.html.twig', array('form' => $form->createView()));
    }

    public function show($id)
    {
        $type = $this->deviceTypeManager->findById($id);
        if (!$type) {
            throw new NotFoundHttpException();
        }

        return $this->twig->render('devicetype/show.html.twig', [
            'type' => $type,
            'devices' => $this->deviceManager->find(['type' => $type->getId()]),
        ]);
    }

    public function edit($id, Request $request)
    {
        $type = $this->deviceTypeManager->findById($id);
        if (!$type) {
            throw new NotFoundHttpException();
        }
        $form = $this->formFactory->create(new DeviceTypeForm(), $type);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->deviceTypeManager->store($type);
            return new RedirectResponse($this->urlGenerator->generate('type_show', ['id' => $type->getId()]));
        }
        return $this->twig->render('devicetype/edit.html.twig', [
            'form' => $form->createView(),
            'type' => $type,
        ]);
    }
}
