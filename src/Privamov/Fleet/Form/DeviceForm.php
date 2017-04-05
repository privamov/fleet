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

namespace Privamov\Fleet\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class DeviceForm extends AbstractType
{
    private $types;

    public function __construct(array $types)
    {
        $this->types = [];
        foreach ($types as $type) {
            $this->types[$type->getId()] = $type->name . ' - ' . $type->manufacturer;
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', 'choice', ['choices' => $this->types, 'empty_value' => 'Choose a device type'])
            ->add('number', 'integer', ['required' => false])
            ->add('imei', 'integer', ['required' => false, 'label' => 'IMEI'])
            ->add('imsi', 'integer', ['required' => false, 'label' => 'IMSI'])
            ->add('nsce', 'integer', ['required' => false, 'label' => 'NSCE'])
            ->add('mac', null, ['required' => false, 'label' => 'MAC address'])
            ->add('purchased', 'date', ['required' => false])
            ->add('price', 'money', ['required' => false])
            ->add('comments', 'textarea', ['required' => false, 'attr' => ['rows' => 4]]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' =>  'Privamov\Fleet\Entity\Device',
        ]);
    }


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'device';
    }
}
