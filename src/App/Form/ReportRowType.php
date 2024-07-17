<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Rotalia\APIBundle\Model\ReportRow;

class ReportRowType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'ReportRow';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => ReportRow::class,
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('count', 'number')
            ->add('productId', 'text')
            ->add('currentPrice', 'number', array(
                'required' => false
            ))
        ;
    }
}
