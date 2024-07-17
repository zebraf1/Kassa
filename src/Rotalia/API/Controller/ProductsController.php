<?php

namespace Rotalia\API\Controller;

use App\Entity\Product;
use App\Entity\User;
use App\Repository\ProductRepository;
use Doctrine\ORM\Query\Expr\Join;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends DefaultController
{
    /**
     * @param ProductRepository $productQuery
     * @param string|null $name
     * @param string|null $productCode
     * @param int|null $productGroupId
     * @param string|null $active
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
        ProductRepository $productQuery,
        #[MapQueryParameter] ?string $name = null,
        #[MapQueryParameter] ?string $productCode = null,
        #[MapQueryParameter] ?int $productGroupId = null,
        #[MapQueryParameter] ?string $active = null,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $limit = 10,
        #[MapQueryParameter] int $conventId = null,
    ): JsonResponse
    {
        $activeConventId = $conventId;
        if ($conventId === null) {
            /** @var User $user */
            $user = $this->getUser();
            $activeConventId = $user->getMember()->getConventId();
        }
        Product::$activeConventId = $activeConventId;

        $offset = ($page - 1) * $limit;
        $query = $productQuery->createQueryBuilder('p');
        $query
            ->orderBy('p.name')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
        ;

        if ($active !== null) {
            $query
                ->innerJoin('p.productInfos', 'pi', Join::WITH, 'pi.status = :status AND pi.conventId = :conventId')
                ->setParameter('status', filter_var($active, FILTER_VALIDATE_BOOLEAN) ? Product::STATUS_ACTIVE : Product::STATUS_DISABLED)
                ->setParameter('conventId', $activeConventId)
            ;
        }

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

        return $this->json([
            'products' => $products,
            'page' => $page,
            'pages' => ceil((int)$count / $limit),
        ]);
    }
}
