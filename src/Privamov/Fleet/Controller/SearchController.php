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

use Privamov\Fleet\Entity\DeviceTypeManager;
use Privamov\Fleet\SearchService;
use Privamov\Fleet\SearchSyntaxException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SearchController
{
    private $deviceTypeManager;
    private $searchService;
    private $urlGenerator;
    private $twig;

    public function __construct(DeviceTypeManager $deviceTypeManager, SearchService $searchService, UrlGeneratorInterface $urlGenerator, \Twig_Environment $twig)
    {
        $this->deviceTypeManager = $deviceTypeManager;
        $this->searchService = $searchService;
        $this->urlGenerator = $urlGenerator;
        $this->twig = $twig;
    }

    public function index(Request $request)
    {
        if (!$request->query->has('q')) {
            return new RedirectResponse($this->urlGenerator->generate('device_index'));
        }

        $q = trim($request->query->get('q'));
        if (strlen($q) === 0) {
            return new RedirectResponse($this->urlGenerator->generate('device_index'));
        }

        try {
            $devices = $this->searchService->search($q);
        } catch (SearchSyntaxException $ex) {
            $error = $ex->getMessage();
            $devices = [];
        }
        if (count($devices) === 1) {
            return new RedirectResponse($this->urlGenerator->generate('device_show', [
                'id' => current($devices)->getId(),
                'q' => $q,
            ]));
        }

        return $this->twig->render('search/index.html.twig', [
            'search' => $q,
            'devices' => $devices,
            'types' => $this->deviceTypeManager->find(),
            'error' => isset($error) ? $error : null,
        ]);
    }
}
