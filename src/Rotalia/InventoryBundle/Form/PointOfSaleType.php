<?php

namespace Rotalia\InventoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PointOfSaleType extends AbstractType
{
    protected $isNew;

    public function __construct($isNew = true)
    {
        $this->isNew = $isNew;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', [
                'label' => 'Nimi:',
                'required' => true,
            ])
            ->add('save', 'submit', [
                'label' => $this->isNew ? 'Tee kassaks' : 'Muuda nime',
            ])
        ;
    }

    public function getName()
    {
        return 'PointOfSaleType';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rotalia\APIBundle\Model\PointOfSale',
        ));
    }
}