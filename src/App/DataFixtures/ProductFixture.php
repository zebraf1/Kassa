<?php

namespace App\DataFixtures;

use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixture extends Fixture
{
    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        // TODO: get productGroup.id
        $beer = 996;
        $food = 997;

        $product1 = new Product();
        $product1
            ->setName('A le Coq Premium')
            ->setProductCode('12345678')
            ->setPrice('1.00')
            ->setAmountType(Product::AMOUNT_TYPE_PIECE)
            ->setStatus(Product::STATUS_ACTIVE)
            ->setSeq(1)
            ->setProductGroupId($beer);
        $manager->persist($product1);

        $product2 = new Product();
        $product2
            ->setName('A le Coq Aleksander')
            ->setPrice('1.50')
            ->setAmountType(Product::AMOUNT_TYPE_PIECE)
            ->setStatus(Product::STATUS_ACTIVE)
            ->setSeq(2)
            ->setProductGroupId($beer);
        $manager->persist($product2);

        $product3 = new Product();
        $product3
            ->setName('A le Coq Pilsner')
            ->setPrice('1.00')
            ->setAmountType(Product::AMOUNT_TYPE_PIECE)
            ->setStatus(Product::STATUS_DISABLED)
            ->setSeq(10);
        $manager->persist($product3);
        $manager->flush();

        $product4 = new Product();
        $product4
            ->setName('Tuc Küpsis')
            ->setPrice('1.00')
            ->setAmountType(Product::AMOUNT_TYPE_PIECE)
            ->setStatus(Product::STATUS_ACTIVE)
            ->setSeq(43)
            ->setProductGroupId($food);
        $manager->persist($product4);

        $manager->flush();
    }
}
