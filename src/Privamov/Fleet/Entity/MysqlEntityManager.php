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

use Symfony\Component\PropertyAccess\PropertyAccessor;

abstract class MysqlEntityManager implements EntityRepository
{
    /**
     * @var \medoo
     */
    protected $db;

    /**
     * @var PropertyAccessor
     */
    protected $propertyAccessor;

    protected $collection;

    protected $className;

    protected $reflClass;

    public function newEntity(array $values = [])
    {
        return $this->hydrate($values);
    }

    protected function __construct(\medoo $db, $className)
    {
        $this->db = $db;
        $this->className = $className;
        $this->collection = strtolower($this->decamelize(str_replace('\\', '_', str_replace(['Privamov\\', 'Entity\\'], '', $className))));
        $this->reflClass = new \ReflectionClass($className);
        $this->propertyAccessor = new PropertyAccessor();
    }

    public function store(Entity $entity)
    {
        if (!$entity->getId()) {
            $lastId = $this->db->insert($this->collection, $this->toArray($entity));
            if (!$lastId) {
                $this->handleSqlError();
            }
            $entity->id = (int)$lastId;
        } else {
            if (false === $this->db->update($this->collection, $this->toArray($entity), ['id' => $entity->getId()])) {
                $this->handleSqlError();
            }
        }
    }

    public function remove(Entity $entity)
    {
        if ($this->db->delete($this->collection, ['id' => $entity->getId()]) === false) {
            $this->handleSqlError();
        }
    }

    public function extract(array $columns, array $where = null)
    {
        $rows = $this->db->select($this->collection, $columns, $where);
        if (false === $rows) {
            $this->handleSqlError();
        }

        return $rows;
    }

    public function extractOne(array $columns, array $where = null)
    {
        $row = $this->db->get($this->collection, $columns, $where);
        if (false === $row) {
            $this->handleSqlError();
        }

        return $row;
    }

    public function find(array $where = null)
    {
        $rows = $this->db->select($this->collection, $this->getFields(), $where);
        if (false === $rows) {
            $this->handleSqlError();
        }

        $entities = [];
        foreach ($rows as $row) {
            $entities[$row['id']] = $this->hydrate($row);
        }

        return $entities;
    }

    public function findOne(array $where)
    {
        $row = $this->db->get($this->collection, $this->getFields(), $where);
        if (false === $row) {
            $this->handleSqlError();
        }

        return $row ? $this->hydrate($row) : null;
    }

    /**
     * @param $id
     * @return null|Entity
     */
    public function findById($id)
    {
        $row = $this->db->get($this->collection, $this->getFields(), ['id' => $id]);
        if (false === $row) {
            $this->handleSqlError();
        }

        return $row ? $this->hydrate($row) : null;
    }

    protected function hydrate(array $values)
    {
        $constructor = $this->reflClass->getConstructor();
        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $key = $this->decamelize($parameter->getName());
            if (isset($values[$key])) {
                if ($parameter->getClass() && $parameter->getClass()->getName() === 'DateTime') {
                    $args[] = new \DateTime($values[$key]);
                } else {
                    $args[] = $values[$key];
                }
            } elseif ($parameter->isDefaultValueAvailable()) {
                $args[] = $parameter->getDefaultValue();
            } elseif ($parameter->allowsNull()) {
                $args[] = null;
            } else {
                throw new \InvalidArgumentException('Unable to affect value to constructor parameter ' . $parameter->getName());
            }
        }
        return $this->reflClass->newInstanceArgs($args);
    }

    protected function camelize($str)
    {
        return strtolower(strtr(ucwords(strtr($str, array('_' => ' ', '.' => '_ ', '\\' => '_ '))), array(' ' => '')));
    }

    protected function decamelize($str)
    {
        return preg_replace_callback('/(^|[a-z])([A-Z])/', function ($matches) {
            return strtolower(strlen($matches[1]) ? $matches[1] . '_' . $matches[2] : $matches[2]);
        }, $str);
    }

    protected function toArray($object)
    {
        $array = [];
        foreach ($this->reflClass->getProperties() as $property) {
            $value = $property->getValue($object);
            if ($property->isStatic()) {
                continue;
            }
            if (isset($value)) {
                if ($value instanceof \DateTime) {
                    $value = $value->format('Y-m-d H:i:s');
                }
                $array[$this->decamelize($property->getName())] = $value;
            }
        }
        return $array;
    }

    protected function getFields()
    {
        $fields = [];
        foreach ($this->reflClass->getProperties() as $property) {
            if (!$property->isStatic()) {
                $fields[] = $this->collection . '.' . $this->decamelize($property->getName());
            }
        }
        return $fields;
    }

    private function handleSqlError()
    {
        $error = $this->db->error();
        if ($error[1]) {
            $log = $this->db->log();
            $reason = sprintf('Error while executing SQL query: %s.' . "\n" . $error[2], $log[count($log) - 1]);
            throw new \RuntimeException($reason);
        }
    }
}
