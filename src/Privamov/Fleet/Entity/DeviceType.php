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

class DeviceType implements Entity
{
    public $id;
    public $manufacturer;
    public $name;
    public $type;

    public static function create()
    {
        return new DeviceType(null, '', '', null);
    }

    public function __construct($id, $manufacturer, $name, $type)
    {
        $this->id = $id;
        $this->manufacturer = $manufacturer;
        $this->name = $name;
        $this->type = $type;
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->name . ($this->manufacturer ? ' (' . $this->manufacturer . ')' : '');
    }
}
