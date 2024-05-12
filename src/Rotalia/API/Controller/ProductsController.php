<?php

namespace Rotalia\API\Controller;

use App\Entity\Product;
use App\Repository\OllekassaProductRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends DefaultController
{
    /**
     * @param OllekassaProductRepository $productQuery
     * @param string|null $name
     * @param string|null $productCode
     * @param int|null $productGroupId
     * @param int $page
     * @param int $limit
     * @param int|null $conventId
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws \Throwable
     */
    #[Route('/products')]
    public function list(
        OllekassaProductRepository $productQuery,
        #[MapQueryParameter] ?string $name = null,
        #[MapQueryParameter] ?string $productCode = null,
        #[MapQueryParameter] ?int $productGroupId = null,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $limit = 10,
        #[MapQueryParameter] int $conventId = null,
    ): JsonResponse
    {
        $offset = ($page - 1) * $limit;
        $query = $productQuery->createQueryBuilder('p');
        $query
            ->orderBy('p.name')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ($name !== null) {
            $query
                ->andWhere('p.name LIKE :name')
                ->setParameter('name', '%'.$name.'%')
            ;
        }

        if ($productCode !== null) {
            $query
                ->andWhere('FIND_IN_SET(:productCode, p.productCode) > 0')
                ->setParameter('productCode', $productCode)
            ;
        }

        if ($productGroupId !== null) {
            $query
                ->andWhere('p.productGroupId = :productGroupId')
                ->setParameter('productGroupId', $productGroupId);
        }

//        $sql = $query->getQuery()->getSQL();
//        return $this->json(['sql' => $query->getQuery()->getSQL()]);


        /** @var Product[] $products */
        $products = $query->getQuery()->getResult();

        $count = $query->select('count(distinct p.id)')->getQuery()->getSingleScalarResult();

        // TODO: fallback to current user convent ID
        Product::$activeConventId = $conventId;

        return $this->json([
            'products' => $products,
            'page' => $page,
            'pages' => ceil((int)$count / $limit),
        ]);
    }
}
