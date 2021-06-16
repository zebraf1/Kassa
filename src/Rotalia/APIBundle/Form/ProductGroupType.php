<?php

namespace Rotalia\APIBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Rotalia\APIBundle\Model\ProductGroup;

class ProductGroupType extends AbstractType
{
    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                'label' => 'Nimi',
                'required' => true,
            ))
            ->add('seq', 'text', array(
                'label' => 'Järjekord',
                'required' => false,
            ))
        ;
    }

    public function getName(): string
    {
        return 'ProductGroupType';
    }

    /**
     * @inheritdoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ProductGroup::class,
        ));
    }
}
