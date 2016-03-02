<?php

namespace Rotalia\InventoryBundle\Form;


use Rotalia\InventoryBundle\Model\ProductQuery;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProductFilterType extends AbstractType
{
    protected $getActive = false;

    public function __construct($getActive = false)
    {
        $this->getActive = $getActive;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $routeParams = $this->getActive ? ['active' => true]: [];
        $builder
            ->add('product', 'ajaxSearch', [
                'label' => 'Toode',
                'required' => false,
                'route' => 'RotaliaInventory_searchProduct',
                'route_params' => $routeParams,
                'query_class' => ProductQuery::create()
            ])
        ;
    }

    public function getName()
    {
        return 'ProductFilterType';
    }
} 