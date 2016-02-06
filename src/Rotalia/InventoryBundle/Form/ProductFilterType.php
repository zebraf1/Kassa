<?php

namespace Rotalia\InventoryBundle\Form;


use Rotalia\InventoryBundle\Model\ProductQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductFilterType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', 'ajaxSearch', [
                'label' => 'Toode',
                'required' => false,
                'route' => 'RotaliaInventory_searchProduct',
                'query_class' => ProductQuery::create()
            ])
        ;
    }

    public function getName()
    {
        return 'ProductFilterType';
    }
} 