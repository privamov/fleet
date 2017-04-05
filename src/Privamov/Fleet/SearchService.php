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

namespace Privamov\Fleet;

use Privamov\Fleet\Entity\Device;
use Privamov\Fleet\Entity\DeviceManager;
use Privamov\Fleet\Entity\LendingManager;

class SearchService
{
    private $deviceManager;
    private $lendingManager;

    public function __construct(DeviceManager $deviceManager, LendingManager $lendingManager)
    {
        $this->deviceManager = $deviceManager;
        $this->lendingManager = $lendingManager;
    }

    /**
     * @param $query
     * @return Device[]
     */
    public function search($query)
    {
        $parts = array_map('trim', explode(' ', $query));
        $devices = [];
        $lendings = [];

        for ($i = 0; $i < count($parts); $i++) {
            $part = $parts[$i];
            $pos = strpos($part, ':');
            if (false === $pos) {
                throw new SearchSyntaxException('Search must be of the form \'operator:value [operator:value [...]]\'.');
            }
            $op = substr($part, 0, $pos);
            $value = substr($part, $pos + 1);

            if ($value[0] === '"') {
                while ($value[strlen($value) - 1] !== '"') {
                    if (++$i >= count($parts)) {
                        throw new SearchSyntaxException('Unfinished quote string (near \'' . $value . '\').');
                    }
                    $value .= ' ' . $parts[$i];
                }
                $value = substr($value, 1, strlen($value) - 2);
            }

            if ('is' === $op) {
                if (!in_array($value, Device::$statuses)) {
                    throw new SearchSyntaxException('Unknown device status \'' . $value . '\' (allowed values are ' . implode(Device::$statuses) . ').');
                }
                $devices['status'][] = $value;
            } elseif ('imei' === $op) {
                $devices['imei'][] = $value;
            } elseif ('number' === $op || 'n' === $op) {
                $devices['number'][] = $value;
            } elseif ('imsi' === $op) {
                $devices['imsi'][] = $value;
            } elseif ('nsce' === $op) {
                $devices['nsce'][] = $value;
            } elseif ('mac' === $op) {
                $devices['mac'][] = $value;
            } elseif ('fname' === $op) {
                if (strpos($value, '%') !== false || strpos($value, '*') !== false || strpos($value, '?') !== false) {
                    $lendings['first_name[~]'][] = str_replace('*', '%', $value);
                } else {
                    $lendings['first_name'][] = $value;
                }
            } elseif ('lname' === $op) {
                if (strpos($value, '%') !== false || strpos($value, '*') !== false || strpos($value, '?') !== false) {
                    $lendings['last_name[~]'][] = str_replace('*', '%', $value);
                } else {
                    $lendings['last_name'][] = $value;
                }
            } elseif ('email' === $op) {
                if (strpos($value, '%') !== false || strpos($value, '*') !== false || strpos($value, '?') !== false) {
                    $lendings['email[~]'][] = str_replace('*', '%', $value);
                } else {
                    $lendings['email'][] = $value;
                }
            } elseif ('started' === $op) {
                if ($value[0] === '>') {
                    $lendings['started[>=]'] = date('Y-m-d', strtotime(substr($value, 1)));
                } elseif ($value[0] === '<') {
                    $lendings['started[<=]'] = date('Y-m-d', strtotime(substr($value, 1)));
                } else {
                    $lendings['started'] = date('Y-m-d', strtotime($value));
                }
            } elseif ('ended' === $op) {
                if ($value[0] === '>') {
                    $lendings['ended[>=]'] = date('Y-m-d', strtotime(substr($value, 1)));
                } elseif ($value[0] === '<') {
                    $lendings['ended[<=]'] = date('Y-m-d', strtotime(substr($value, 1)));
                } else {
                    $lendings['ended'] = date('Y-m-d', strtotime($value));
                }
            } elseif ('token' === $op) {
                $lendings['token'] = $value;
            } elseif ('segment' === $op) {
                $lendings['segment'] = $value;
            } else {
                throw new SearchSyntaxException('Unknown operator "' . $op . '" (near "' . $op . ':' . $value . ').');
            }
        }

        $devices = array_filter($devices);
        $results = [];
        if (!empty($devices)) {
            $results = array_map(function ($row) {
                return $row['id'];
            }, $this->deviceManager->extract(['id'],['AND' => $devices]));
        }

        $lendings = array_filter($lendings);
        if (!empty($lendings)) {
            $newResults = array_map(function ($row) {
                return $row['device'];
            }, $this->lendingManager->extract(['device'], ['AND' => $lendings]));
            $results = $results ? array_intersect($results, $newResults) : $newResults;
        }

        if (!empty($results)) {
            return $this->deviceManager->find(['id' => $results]);
        }
        return [];
    }
}
