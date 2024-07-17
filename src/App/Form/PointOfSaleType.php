<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PointOfSaleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', 'text', [
                'label' => 'Nimi',
                'required' => true,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'PointOfSaleType';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rotalia\APIBundle\Model\PointOfSale',
        ));
    }
}
