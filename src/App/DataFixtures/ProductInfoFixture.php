<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductInfo;
use App\Repository\OllekassaProductRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductInfoFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tallinn = 5982;
        $tartu = 5983;

        /** @var OllekassaProductRepository $productRepo */
        $productRepo = $manager->getRepository(Product::class);
        $product1 = $productRepo->findOneBy(['name' => 'A le Coq Premium']);
        $product2 = $productRepo->findOneBy(['name' => 'A le Coq Aleksander']);
        $product3 = $productRepo->findOneBy(['name' => 'A le Coq Pilsner']);
        $product4 = $productRepo->findOneBy(['name' => 'Tuc Küpsis']);

        $info = new ProductInfo();
        $info
            ->setProduct($product1)
            ->setConventId($tallinn)
            ->setPrice('1.00')
            ->setStatus(Product::STATUS_ACTIVE)
            ->setStorageCount(5)
            ->setWarehouseCount(15)
        ;
        $manager->persist($info);

        $info = new ProductInfo();
        $info
            ->setProduct($product1)
            ->setConventId($tartu)
            ->setPrice('1.10')
            ->setStatus(Product::STATUS_ACTIVE)
        ;
        $manager->persist($info);

        $info = new ProductInfo();
        $info
            ->setProduct($product2)
            ->setConventId($tallinn)
            ->setPrice('1.50')
            ->setStatus(Product::STATUS_ACTIVE)
        ;
        $manager->persist($info);

        $info = new ProductInfo();
        $info
            ->setProduct($product2)
            ->setConventId($tartu)
            ->setPrice('1.50')
            ->setStatus(Product::STATUS_DISABLED)
        ;
        $manager->persist($info);

        $info = new ProductInfo();
        $info
            ->setProduct($product3)
            ->setConventId($tallinn)
            ->setPrice('1.00')
            ->setStatus(Product::STATUS_DISABLED)
        ;
        $manager->persist($info);

        $info = new ProductInfo();
        $info
            ->setProduct($product3)
            ->setConventId($tartu)
            ->setPrice('1.00')
            ->setStatus(Product::STATUS_DISABLED)
        ;
        $manager->persist($info);

        $info = new ProductInfo();
        $info
            ->setProduct($product4)
            ->setConventId($tallinn)
            ->setPrice('1.00')
            ->setStatus(Product::STATUS_ACTIVE)
        ;
        $manager->persist($info);

        $info = new ProductInfo();
        $info
            ->setProduct($product4)
            ->setConventId($tartu)
            ->setPrice('1.00')
            ->setStatus(Product::STATUS_ACTIVE)
        ;
        $manager->persist($info);

        $manager->flush();
    }
}
