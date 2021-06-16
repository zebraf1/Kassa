<?php

namespace Rotalia\APIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Rotalia\APIBundle\Model\ReportRow;

class ReportRowType extends AbstractType
{
    public function getName(): string
    {
        return 'ReportRow';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ReportRow::class,
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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
