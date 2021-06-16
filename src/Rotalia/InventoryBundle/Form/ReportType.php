<?php

namespace Rotalia\InventoryBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ReportType extends AbstractType
{
    private $isEditForm;

    /**
     * @param bool $isEditForm  Includes only saved report rows when edit form
     */
    public function __construct($isEditForm = false)
    {
        $this->isEditForm = $isEditForm;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $reportRowsFormName = $this->isEditForm ? 'reportRowsFormEdit' : 'reportRowsForm';
        $builder
            ->add('cash', 'number', array(
                'label' => 'Sularaha:',
                'required' => false,
            ))
            ->add($reportRowsFormName, 'collection', [
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
            'data_class' => 'Rotalia\APIBundle\Model\Report',
        ));
    }
} 