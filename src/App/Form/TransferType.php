<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
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

    public function getBlockPrefix(): string
    {
        return 'TransferType';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => 'Rotalia\APIBundle\Model\Transfer',
        ));
    }
}
