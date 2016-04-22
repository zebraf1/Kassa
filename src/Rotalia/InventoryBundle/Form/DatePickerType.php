<?php

namespace Rotalia\InventoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class DatePickerType
 *
 * Adds jQuery date picker functionality to a date field
 *
 * @package Rotalia\InventoryBundle\Form
 */
class DatePickerType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'format' => 'dd.MM.yyyy',
        ]);
    }

    public function getParent()
    {
        return 'date';
    }

    public function getName()
    {
        return 'datePicker';
    }
}
