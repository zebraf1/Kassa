<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('cash', 'number')
            ->add('source', 'text', array(
                'required' => false
            ))
            ->add('target', 'text', array(
                'required' => false
            ))
            ->add('reportRows', 'collection', [
                'type' => new ReportRowType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'Report';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Rotalia\APIBundle\Model\Report',
            'csrf_protection' => false,
            'cascade_validation' => true,
        ]);
    }
}