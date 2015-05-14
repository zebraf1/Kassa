<?php

namespace Rotalia\InventoryBundle\Form;


use Rotalia\InventoryBundle\Classes\XClassifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Nimi',
                'required' => true,
            ))
            ->add('price', 'text', array(
                'label' => 'Hind',
                'required' => true,
            ))
            ->add('amount_type_id', 'choice', array(
                'label' => 'Ühik',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => XClassifier::$AMOUNT_TYPES
            ))
            ->add('status_id', 'choice', array(
                'label' => 'Staatus',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => XClassifier::$STATUSES
            ))
        ;
    }

    public function getName()
    {
        return 'ProductType';
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