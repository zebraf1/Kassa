<?php

namespace App\Form;

use App\Entity\Product;
use Rotalia\APIBundle\Classes\XClassifier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // TODO: fix types and validation
        /**
         * App\Entity\Product:
         * properties:
         * name:
         * - NotBlank: ~
         * price:
         * - NotBlank: ~
         * - Type:
         * type: numeric
         * message: Väärtus peab olema number.
         * - GreaterThanOrEqual: 0
         * amount_type:
         * - NotBlank: ~
         * amount:
         * - NotBlank: ~
         * - Type:
         * type: numeric
         * message: Väärtus peab olema number.
         * - GreaterThanOrEqual: 0
         * status:
         * - NotBlank: ~
         * getters:
         * productGroupValid:
         * - 'IsTrue':
         * message: 'Tootegruppi pole olemas'
         */
        $builder
            ->add('name', 'text', array(
                'label' => 'Nimi',
                'required' => true,
            ))
            ->add('price', 'text', array(
                'label' => 'Hind',
                'required' => true,
            ))
            ->add('amount', 'text', array(
                'label' => 'Kogus',
                'required' => true,
            ))
            ->add('amountType', 'choice', array(
                'label' => 'Ühik',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => XClassifier::$AMOUNT_TYPES
            ))
            ->add('status', 'choice', array(
                'label' => 'Staatus',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => XClassifier::$STATUSES
            ))
            ->add('resourceType', 'choice', array(
                'label' => 'Tüüp',
                'required' => true,
                'expanded' => false,
                'multiple' => false,
                'choices' => XClassifier::$RESOURCE_TYPES
            ))
            ->add('productCode', 'text', array(
                'label' => 'Tootekood',
                'required' => false,
            ))
            ->add('productGroupId', 'text', [
                'label' => 'Toote grupp',
                'required' => false,
            ])
        ;
    }

    public function getBlockPrefix(): string
    {
        return 'ProductType';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => Product::class,
        ));
    }
}

