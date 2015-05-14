<?php

namespace Rotalia\InventoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ProductSeqType
 *
 * Form for reordering products in the list
 *
 * @package Rotalia\InventoryBundle\Form
 */
class ProductSeqType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'hidden', array(
                'required' => true,
            ))
            ->add('seq', 'hidden', array(
                'required' => true,
            ))
        ;
    }

    public function getName()
    {
        return 'ProductSeqType';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rotalia\InventoryBundle\Model\Product',
        ));
    }
} 