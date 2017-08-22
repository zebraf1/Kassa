<?php

namespace Rotalia\APIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CreditNettingRowType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nettingDone', 'integer', [
                'label' => 'Tehtud'
            ])
        ;
    }

    public function getName()
    {
        return 'CreditNettingRowType';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rotalia\InventoryBundle\Model\CreditNettingRow',
        ));
    }
}
