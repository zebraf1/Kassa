<?php

namespace Rotalia\APIBundle\Model\Form;

class ProductList
{
    public $products;

    public function __construct($products)
    {
        $this->products = $products;
    }
}
