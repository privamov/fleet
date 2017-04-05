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

class Device implements Entity
{
    const AVAILABLE = 'available';
    const LENT = 'lent';
    const LOST = 'lost';
    public static $statuses = [self::AVAILABLE, self::LENT, self::LOST];

    public $id;
    public $type;
    public $number;
    public $mac;
    public $imei;
    public $imsi;
    public $nsce;
    public $purchased;
    public $created;
    public $updated;
    public $price;
    public $status;
    public $comments;

    public function __construct($id, $type, $number, $mac, $imei, $imsi, $nsce, $purchased, $created, $updated, $price, $status, $comments)
    {
        $this->id = $id;
        $this->type = $type;
        $this->number = $number;
        $this->mac = $mac;
        $this->imei = $imei;
        $this->imsi = $imsi;
        $this->nsce = $nsce;
        $this->purchased = $purchased;
        $this->created = $created ?: new \DateTime();
        $this->updated = $updated;
        $this->price = $price;
        $this->status = $status ?: self::AVAILABLE;
        $this->comments = $comments;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isLent()
    {
        return $this->status === self::LENT;
    }

    public function isLost()
    {
        return $this->status === self::LOST;
    }

    public function isAvailable()
    {
        return $this->status === self::AVAILABLE;
    }

    public function __toString()
    {
        if ($this->number) {
            return 'Device #' . $this->number;
        } elseif ($this->imei) {
            return 'Device IMEI ' . $this->imei;
        } elseif ($this->mac) {
            return 'Device MAC ' . $this->mac;
        } else {
            return 'Unknown device';
        }
    }
}
