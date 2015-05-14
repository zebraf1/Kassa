<?php

namespace Rotalia\InventoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReportType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cash', 'number', array(
                'label' => 'Sularahajääk:',
                'required' => true,
            ))
            ->add('reportRowsForm', 'collection', [
                'type' => new ReportRowType()
            ])
        ;
    }

    public function getName()
    {
        return 'Report';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rotalia\InventoryBundle\Model\Report',
        ));
    }
} 