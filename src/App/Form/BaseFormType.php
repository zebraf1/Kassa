<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class BaseFormType extends AbstractType
{
    /**
     * Entity class name
     * @var string
     */
    public static string $modelClass;

    /**
     * Entity name in REST request/response
     * @var string
     */
    public static string $modelName;

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return static::$modelName;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false, // Disable for REST api
            'data_class' => static::$modelClass,
        ]);
    }
}
