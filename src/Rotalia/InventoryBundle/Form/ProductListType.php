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
class ProductListType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('products', 'collection', array(
                'type' => new ProductSeqType(),
            ))
        ;
    }

    public function getName()
    {
        return 'ProductListType';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rotalia\APIBundle\Model\Form\ProductList',
            'csrf_protection' => false,
        ));
    }
} 