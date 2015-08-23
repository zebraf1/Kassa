<?php

namespace Rotalia\InventoryBundle\Twig;


class RotaliaTwigExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('custom_date', array($this, 'customDateFilter')),
        );
    }

    /**
     * @param \DateTime $date
     * @param $format
     * @param string $locale
     * @return string
     */
    public function customDateFilter($date, $format = '%e. %b %Y', $locale = 'et_EE')
    {
        if (!$date) {
            return '';
        }

        setlocale(LC_TIME, $locale);
        return strftime($format, $date->getTimestamp());
    }

    public function getName()
    {
        return 'rotalia_extension';
    }
}