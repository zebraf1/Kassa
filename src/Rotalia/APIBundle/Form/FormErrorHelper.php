<?php

namespace Rotalia\APIBundle\Form;


use Symfony\Component\Form\FormInterface;

class FormErrorHelper
{
    /**
     * Return all errors of the form and its children
     *
     * @param FormInterface $form
     * @param array $errors
     * @return array
     */
    public static function getErrors(FormInterface $form, &$errors = [])
    {
        if (!empty((string)$form->getErrors())) {
            $errors[$form->getName()] = '';
            foreach ($form->getErrors() as $formError) {
                $errors[$form->getName()] .= $formError->getMessage();
            }
        }

        foreach ($form->all() as $subForm) {
            self::getErrors($subForm, $errors);
        }

        return $errors;
    }
}
