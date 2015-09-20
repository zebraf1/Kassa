<?php

namespace Rotalia\InventoryBundle\Form;


use Rotalia\InventoryBundle\Model\ProductQuery;
use Rotalia\UserBundle\Model\MemberQuery;
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
            ->add('product', 'ajaxSearch', [
                'label' => 'Toode',
                'required' => false,
                'route' => 'RotaliaInventory_searchProduct',
                'query_class' => ProductQuery::create()
            ])
            ->add('member', 'ajaxSearch', [
                'label' => 'Kasutaja',
                'required' => false,
                'route' => 'RotaliaInventory_searchMember',
                'query_class' => MemberQuery::create()
            ])
            ->add('save', 'submit', [
                'label' => 'Otsi',
            ])
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
