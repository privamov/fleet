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
use Privamov\Fleet\Entity\Lending;
use Privamov\Fleet\Entity\LendingManager;
use Privamov\Fleet\Form\LendingForm;
use Privamov\Fleet\Form\NewLendingForm;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LendingController
{
    private $deviceManager;
    private $lendingManager;
    private $formFactory;
    private $urlGenerator;
    private $twig;

    public function __construct(DeviceManager $deviceManager, LendingManager $lendingManager, FormFactoryInterface $formFactory, UrlGeneratorInterface $urlGenerator, \Twig_Environment $twig)
    {
        $this->deviceManager = $deviceManager;
        $this->lendingManager = $lendingManager;
        $this->formFactory = $formFactory;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function create($id, Request $request)
    {
        /** @var Device $device */
        $device = $this->deviceManager->findById($id);
        if (!$device) {
            throw new NotFoundHttpException();
        }

        if (!$device->isAvailable()) {
            throw new AccessDeniedException();
        }

        $lending = $this->lendingManager->newEntity(['device' => $device->getId()]);
        $form = $this->formFactory->create(new NewLendingForm(), $lending);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->lendingManager->store($lending);
            $device->status = 'lent';
            $this->deviceManager->store($device);

            return new RedirectResponse($this->urlGenerator->generate('device_show', ['id' => $device->getId()]));
        }

        return $this->twig->render('lending/new.html.twig', [
            'device' => $device,
            'form' => $form->createView(),
        ]);
    }

    public function edit($id, Request $request)
    {
        /** @var Lending $lending */
        $lending = $this->lendingManager->findById($id);
        if (!$lending) {
            throw new NotFoundHttpException();
        }

        /** @var Device $device */
        $device = $this->deviceManager->findById($lending->device);
        if (!$device) {
            throw new NotFoundHttpException();
        }

        $form = $this->formFactory->create(new LendingForm(), $lending);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->lendingManager->store($lending);

            $lastLending = $this->lendingManager->getLastLending($device->getId());
            if ($lastLending && $lending->getId() === $lastLending->getId()) {
                if ($lending->status === 'lost' && $device->status !== 'lost') {
                    $device->status = 'lost';
                    $this->deviceManager->store($device);
                } elseif ($lending->status === 'back' && $device->status !== 'available') {
                    $device->status = 'available';
                    $this->deviceManager->store($device);
                } elseif ($lending->status === 'lent' && $device->status !== 'lent') {
                    $device->status = 'lent';
                    $this->deviceManager->store($device);
                }
            }

            return new RedirectResponse($this->urlGenerator->generate('device_show', ['id' => $lending->device]));
        }

        return $this->twig->render('lending/edit.html.twig', [
            'form' => $form->createView(),
            'lending' => $lending,
        ]);
    }

    public function delete($id, Request $request)
    {
        /** @var Lending $lending */
        $lending = $this->lendingManager->findById($id);
        if (!$lending) {
            throw new NotFoundHttpException();
        }

        /** @var Device $device */
        $device = $this->deviceManager->findById($lending->device);
        if (!$device) {
            throw new NotFoundHttpException();
        }

        if ($request->getMethod() === 'POST') {
            $lastLending = $this->lendingManager->getLastLending($device->getId());
            $this->lendingManager->remove($lending);

            if (
                $lastLending
                && $lending->getId() === $lastLending->getId()
                && !$device->isAvailable()
            ) {
                $device->status = 'available';
                $this->deviceManager->store($device);
            }

            return new RedirectResponse($this->urlGenerator->generate('device_show', ['id' => $device->getId()]));
        }

        return $this->twig->render('lending/delete.html.twig', [
            'lending' => $lending,
            'device' => $device,
        ]);
    }

    public function back($id)
    {
        /** @var Lending $lending */
        $lending = $this->lendingManager->findById($id);
        if (!$lending) {
            throw new NotFoundHttpException();
        }

        /** @var Device $device */
        $device = $this->deviceManager->findById($lending->device);
        if (!$device) {
            throw new NotFoundHttpException();
        }

        $lending->ended = date('Y/m/d H:i:s');
        $lending->status = 'back';
        $this->lendingManager->store($lending);

        $device->status = 'available';
        $this->deviceManager->store($device);

        return new RedirectResponse($this->urlGenerator->generate('device_show', ['id' => $lending->device]));
    }

    public function apiShow($token)
    {
        /** @var Lending $lending */
        $lending = $this->lendingManager->findOne(['token' => $token]);
        if (!$lending) {
            throw new NotFoundHttpException();
        }

        /** @var Device $device */
        $device = $this->deviceManager->findById($lending->device);
        if (!$device) {
            throw new NotFoundHttpException();
        }

        $data = [
            'firstName' => $lending->firstName,
            'lastName' => $lending->lastName,
            'email' => $lending->email,
            'imei' => $device->imei,
            'segment' => $lending->segment,
            'status' => $lending->status,
            'started' => $lending->started ? $lending->started->format('Y-m-d\TH:i:sO') : null,
            'ended' => $lending->ended ? $lending->ended->format('Y-m-d\TH:i:sO') : null,
        ];

        return new JsonResponse($data);
    }
}
