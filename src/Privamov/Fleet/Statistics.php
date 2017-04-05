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

class Statistics
{
    public static function groupBy(array $rawData, $rowName, $columnName)
    {
        $data = ['rows' => [], 'total' => []];
        foreach ($rawData as $row) {
            $rowKey = $row[$rowName] ?: '(unknown)';
            if (!isset($data['rows'][$rowKey])) {
                $data['rows'][$rowKey] = [];
            }
            $columnKey = $row[$columnName] ?: '(unknown)';
            $data['rows'][$rowKey][$columnKey] = (int)$row['c'];

            if (!isset($data['total'][$columnKey])) {
                $data['total'][$columnKey] = 0;
            }
            $data['total'][$columnKey] += (int)$row['c'];
        }
        foreach ($data['rows'] as $rowKey => $row) {
            $data['rows'][$rowKey]['_total'] = array_sum($data['rows'][$rowKey]);
        }
        $data['count'] = array_sum($data['total']);

        return $data;
    }
}
