<?php

namespace Rotalia\InventoryBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class AjaxSearchType
 *
 * Form element with ajax search functional input.
 * Parameter route - route name
 * Parameter query_class - an instance of a query class, ie MemberQuery::create();
 *
 * @package Rotalia\InventoryBundle\Form
 */
class AjaxSearchType extends AbstractType
{
    protected $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $queryClass = $options['query_class'];

        if (!($queryClass instanceof \ModelCriteria)) {
            throw new \Exception('query_class must be instance of ModelCriteria, use MyQuery::create()');
        }

        $choiceOptions = [];

        //Pass label and required attributes to child form
        if (isset($options['label'])) {
            $choiceOptions['label'] = $options['label'];
        }
        if (isset($options['required'])) {
            $choiceOptions['required'] = $options['required'];
        }

        //Add select2 class and data url
        $choiceOptions['attr'] = array_merge($options['attr'], [
            'class' => 'select2-ajax-search',
            'data-ajax--url' => $this->router->generate($options['route']),
        ]);

        //Add event listeners to set selected choice dynamically as choices. This is required so form submit is valid
        $listener = function (FormEvent $event) use ($choiceOptions, $queryClass) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!empty($data)) {
                $id = current($data);
                if ($id && $object = $queryClass->findPk($id)) {
                    $choiceOptions['choices'] = [
                        $id => $object->getAjaxName()
                    ];
                }
            }

            $form->add('id', 'choice', $choiceOptions);
        };

        $builder->addEventListener(FormEvents::PRE_SET_DATA, $listener);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $listener);
    }

    /**
     * @return string
     */
    public function getName() {
        return 'ajaxSearch';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired(['route', 'query_class']);
        $resolver->setDefaults([
            'choices' => [],
            'choices_as_value' => true
        ]);
    }
}
