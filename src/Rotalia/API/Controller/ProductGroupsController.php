<?php

namespace Rotalia\API\Controller;

use App\Dto\ProductGroupDto;
use App\Entity\ProductGroup;
use App\Repository\ProductGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class ProductGroupsController extends DefaultController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Throwable
     */
    #[Route('/productGroups', methods: ['GET'])]
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

    /**
     * @throws Throwable
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    #[Route('/productGroups', methods: ['POST'])]
    public function create(
        EntityManagerInterface $em,
        Request $request,
        ValidatorInterface $validator,
    ): JsonResponse
    {
        $this->requireAdmin();
        $data = $request->getPayload()->all();

        $dto = new ProductGroupDto(
            $data['name'] ?? '',
            $data['seq'] ?? null,
        );
        $violationList = $validator->validate($dto);
        if ($violationList->has(0)) {
            return $this->json([
                'message' => $violationList->get(0)->getMessage(),
            ], 400);
        }
        $group = new ProductGroup();
        $group
            ->setName($dto->name)
            ->setSeq($dto->seq)
        ;

        $em->persist($group);

        return $this->json([
            'productGroup' => $group,
        ], 201);
    }
}
