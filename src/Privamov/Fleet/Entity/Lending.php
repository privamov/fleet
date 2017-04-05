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

namespace Privamov\Fleet\Entity;

use Rhumsaa\Uuid\Uuid;

class Lending implements Entity
{
    const LENT = 'lent';
    const BACK = 'back';
    const LOST = 'lost';

    public $id;
    public $firstName;
    public $lastName;
    public $email;
    public $phone;
    public $device;
    public $status;
    public $segment;
    public $comments;
    public $started;
    public $ended;
    public $token;

    public function __construct(
        $id,
        $firstName,
        $lastName,
        $email,
        $phone,
        $device,
        $token = null,
        $status = self::LENT,
        $segment = 'privamov',
        $comments = '',
        \DateTime $started = null,
        \DateTime $ended = null
    )
    {
        $this->id = (int) $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->phone = $phone;
        $this->device = (int) $device;
        $this->status = $status;
        $this->segment = $segment;
        $this->comments = $comments;
        $this->token = $token ?: Uuid::uuid4()->toString();
        $this->started = $started ?: new \DateTime();
        $this->ended = $ended;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function isLent()
    {
        return $this->status === self::LENT;
    }

    public function isLost()
    {
        return $this->status === self::LOST;
    }

    public function isBack()
    {
        return $this->status === self::BACK;
    }
}
