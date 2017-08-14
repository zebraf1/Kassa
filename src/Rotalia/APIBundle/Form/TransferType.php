<?php

namespace Rotalia\APIBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TransferType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('memberId', 'integer', [
                'label' => 'Kasutaja'
            ])
            ->add('sum', 'number', [
                'label' => 'Summa (€)'
            ])
            ->add('comment', 'text', [
                'label' => 'Kommentaar'
            ])
        ;
    }

    public function getName()
    {
        return 'TransferType';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rotalia\InventoryBundle\Model\Transfer',
        ));
    }
}
