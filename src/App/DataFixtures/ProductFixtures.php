<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\ProductGroup;
use App\Entity\ProductInfo;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ProductFixtures extends Fixture
{
    /**
     * @param ObjectManager $manager
     * @return void
     */
    public function load(ObjectManager $manager): void
    {
        $beerGroup = new ProductGroup();
        $beerGroup
            ->setName('Õlu')
            ->setSeq(1);
        $manager->persist($beerGroup);

        $foodGroup = new ProductGroup();
        $foodGroup
            ->setName('Toit')
            ->setSeq(2);
        $manager->persist($foodGroup);

        $product1 = new Product();
        $product1
            ->setName('A le Coq Premium')
            ->setProductCode('12345678')
            ->setPrice('1.00')
            ->setAmountType(Product::AMOUNT_TYPE_PIECE)
            ->setStatus(Product::STATUS_ACTIVE)
            ->setSeq(1)
            ->setProductGroup($beerGroup);
        $manager->persist($product1);

        $product2 = new Product();
        $product2
            ->setName('A le Coq Aleksander')
            ->setPrice('1.50')
            ->setAmountType(Product::AMOUNT_TYPE_PIECE)
            ->setStatus(Product::STATUS_ACTIVE)
            ->setSeq(2)
            ->setProductGroup($beerGroup);
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
            ->setProductGroup($foodGroup);
        $manager->persist($product4);

        // TODO: get convent
        $tallinn = 5982;
        $tartu = 5983;

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
