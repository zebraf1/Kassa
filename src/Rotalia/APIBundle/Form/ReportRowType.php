<?php

namespace Rotalia\APIBundle\Form;


use Rotalia\InventoryBundle\Form\ReportRowType as BaseReportRowType;
use Symfony\Component\Form\FormBuilderInterface;

class ReportRowType extends BaseReportRowType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('count', 'number')
            ->add('productId', 'text')
        ;
    }
}
