<?php

namespace Rotalia\InventoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ProductPurchaseFilterForm
 *
 * @package Rotalia\InventoryBundle\Form
 */
class ProductPurchaseFilterForm  extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date', 'datePicker', [
                'label' => 'Kuupäev',
                'required' => false,
            ])
            ->add('product', 'text', [
                'label' => 'Toode',
                'required' => false,
            ])
            ->add('member', 'text', [
                'label' => 'Kasutaja',
                'required' => false,
            ])
            ->add('save', 'submit', [
                'label' => 'Otsi',
            ])
            ->setMethod('GET')
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
            'csrf_protection' => false,
        ));
    }
}
