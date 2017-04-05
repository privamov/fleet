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


use Privamov\Fleet\Statistics;

class LendingManager extends MysqlEntityManager
{
    public function __construct(\medoo $db)
    {
        parent::__construct($db,  'Privamov\Fleet\Entity\Lending');
    }

    public function findByDevice($deviceId)
    {
        return $this->find(['ORDER' => 'started DESC', 'device' => $deviceId]);
    }

    public function getLastLending($deviceId)
    {
        return $this->findOne(['LIMIT' => 1, 'ORDER' => 'started DESC', 'device' => $deviceId]);
    }

    public function countByTypeAndSegment()
    {
        $data = $this->db->query('select segment, d.type, count(1) as c
              from fleet_lending l
              join fleet_device d on l.device = d.id
              where l.status = \'lent\'
              group by segment, d.type')
            ->fetchAll();
        return Statistics::groupBy($data, 'type', 'segment');
    }

    public function countByTypeAndStatus()
    {
        $data = $this->db->query('select type, status, count(1) as c
              from fleet_device
              group by type, status')->fetchAll();
        return Statistics::groupBy($data, 'type', 'status');
    }
}
