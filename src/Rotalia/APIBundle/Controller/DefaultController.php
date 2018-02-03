<?php

namespace Rotalia\APIBundle\Controller;


use ModelCriteria;
use Rotalia\InventoryBundle\Controller\BaseController;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderInterface;

/**
 * Base controller for API bundle controllers
 *
 * Class DefaultController
 * @package Rotalia\APIBundle\Controller
 */
class DefaultController extends BaseController
{
    /**
     * Note: CsrfProviderInterface is deprecated. Upgrade FosUserBundle to fix this and use CsrfTokenManagerInterface
     * @return CsrfProviderInterface
     */
    protected function getCSRFProvider()
    {
        return $this->get('form.csrf_provider'); // todo: use security.csrf.token_manager since symfony 3.0
    }

    /**
     * @param $query ModelCriteria
     * @param $limit integer
     * @param $offset integer
     * @return string Content-Range header
     */
    protected function limitQuery($query, $limit, $offset) {

        $rowCount = $query->count();

        if ($offset >= $rowCount) {
            $offset = 0;
        }

        if ($limit) {
            $limit = min($limit, $rowCount);
            $query
                ->limit($limit)
                ->offset($offset)
            ;
        } else {
            $limit = $rowCount;
        }

        return sprintf('%d-%d/%d', $offset, $offset + $limit, $rowCount);

    }
}
