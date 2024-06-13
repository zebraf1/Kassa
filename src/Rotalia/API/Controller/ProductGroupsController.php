<?php

namespace Rotalia\API\Controller;

use App\Entity\ProductGroup;
use App\Repository\ProductGroupRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class ProductGroupsController extends DefaultController
{
    #[Route('/productGroups')]
    public function list(
        ProductGroupRepository $groupQuery,
    ): JsonResponse
    {
        $query = $groupQuery->createQueryBuilder('g');
        $query
            ->orderBy('g.seq')
        ;

        /** @var ProductGroup[] $groups */
        $groups = $query->getQuery()->getResult();

        return $this->json([
            'productGroups' => $groups,
        ]);
    }
}
