<?php

namespace Rotalia\APIBundle\Form;

use Rotalia\InventoryBundle\Model\ReportPeer;
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

    public function getName()
    {
        return 'Report';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Rotalia\InventoryBundle\Model\Report',
            'csrf_protection' => false,
            'cascade_validation' => true,
        ]);
    }
}