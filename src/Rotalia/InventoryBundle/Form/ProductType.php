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
            ->add('amount', 'text', array(
                'label' => 'Kogus',
                'required' => true,
            ))
            ->add('amountType', 'choice', array(
                'label' => 'Ühik',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => XClassifier::$AMOUNT_TYPES
            ))
            ->add('status', 'choice', array(
                'label' => 'Staatus',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => XClassifier::$STATUSES
            ))
            ->add('productCode', 'text', array(
                'label' => 'Tootekood',
                'required' => false,
            ))
            ->add('productGroupId', 'model', [
                'label' => 'Toote grupp',
                'class' => 'Rotalia\InventoryBundle\Model\ProductGroup',
                'required' => false,
            ])
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
