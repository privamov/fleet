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

namespace Privamov\Librarian;

class Librarian
{
    private $path;
    private $values;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getInt($key, $default = null)
    {
        $this->parse();

        return isset($this->values[$key]) ? (int)$this->values[$key] : $default;
    }

    public function getDouble($key, $default = null)
    {
        $this->parse();

        return isset($this->values[$key]) ? (double)$this->values[$key] : $default;
    }

    public function getString($key, $default = null)
    {
        $this->parse();

        return isset($this->values[$key]) ? $this->values[$key] : $default;
    }

    public function getKeys($path)
    {
        $this->parse();
        $matches = [];
        foreach (array_keys($this->values) as $key) {
            if (strpos($key, $path) === 0) {
                $key = substr($key, strlen($path) + 1);
                if (strpos($key, '/') !== false) {
                    $key = substr($key, 0, strpos($key, '/'));
                }
                $matches[] = $key;
            }
        }

        return $matches;
    }

    private function parse()
    {
        if (!isset($this->values)) {
            foreach (array_filter(file($this->path)) as $line) {
                $line = trim($line);
                if (!$line || $line[0] === '#') {
                    continue;
                }
                list($key, $value) = explode('=', $line);
                $key = trim($key);
                $value = trim($value);
                if ($key && $value) {
                    $this->values[$key] = $value;
                }
            }
        }
    }
}
