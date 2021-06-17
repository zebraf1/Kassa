<?php

namespace Rotalia\APIBundle\Tests\Controller;

use Rotalia\APIBundle\Model\ProductQuery;
use Rotalia\UserBundle\Model\ConventQuery;
use Symfony\Component\BrowserKit\Cookie;

class PurchaseControllerTest extends WebTestCase
{
    public function testPaymentPosLoggedInOtherConvent()
    {
        $posHash = 'abcdef1234567890abcdef1234567890'; #Tallinn PointOfSale
        self::$client->getCookieJar()->set(new Cookie('pos_hash', $posHash));

        $this->loginSimpleUser();

        $product = ProductQuery::create()->findOneByName('A le Coq Premium');
        $conventTartu = ConventQuery::create()->findOneByName('Tartu');

        self::$client->request('POST', '/api/purchase/credit/', [
            'conventId' => $conventTartu->getId(), // Wrong conventID
            'basket' => [
                'id' => $product->getId(),
                'count' => 1,
                'price' => $product->getPrice(),
            ],
        ]);

        $response = self::$client->getResponse();
        $this->assertEquals(400, $response->getStatusCode());
        $content = $response->getContent();
        $result = json_decode($content, true);
        $this->assertArrayHasKey('message', $result, join(',', array_keys($result)));
        $this->assertEquals('See brauser on määratud müügipunktiks (Tallinn) ja ei luba valitud konvendist ostu', $result['message']);
    }

    /**
     * The user has -10€ in Tartu and 20€ in Tallinn.
     * The credit limit for this user is -25€.
     *
     * The user should be able to make an 17.6€ (1.1*16€) purchase in Tartu,
     * because the total credit is checked.
     * If only the credit in Tartu is checked, then this test would fail:
     * -10€ - 17.6€ = -27.6€ < -25€
     */
    public function testPurchaseSuccessEvenIfCreditInWrongConvent()
    {
        $this->loginSimpleUser();

        $product = ProductQuery::create()->findOneByName('A le Coq Premium');
        $conventTartu = ConventQuery::create()->findOneByName('Tartu');

        self::$client->request('POST', '/api/purchase/credit/', [
            'conventId' => $conventTartu->getId(),
            'basket' => [[
                'id' => $product->getId(),
                'count' => "16",
                'price' => $product->getPrice(),
            ]],
        ]);

        $response = self::$client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $content = $response->getContent();
        $result = json_decode($content, true);
        $this->assertArrayHasKey('status', $result, join(',', array_keys($result)));
        $this->assertEquals([
            'data' => [
                'totalSumCents' => 1760,
                'newCredit'     => -7.6
            ],
            'status' => 'success',
        ], $result);
    }

    /**
     * Purchase should be not allowed when there is not enough credit:
     * Current credit: -10€ + 20€ = 10€
     * Purchase: 32 * 1.1€ = 35.2€
     * Credit limit: -25€
     * Final credit: 10€ - 35.2€ = -25.2€ < -25€
     */
    public function testPurchaseFailIfNotEnoughCredit()
    {
        $this->loginSimpleUser();

        $product = ProductQuery::create()->findOneByName('A le Coq Premium');
        $conventTartu = ConventQuery::create()->findOneByName('Tartu');

        self::$client->request('POST', '/api/purchase/credit/', [
            'conventId' => $conventTartu->getId(),
            'basket' => [[
                'id' => $product->getId(),
                'count' => "32",
                'price' => $product->getPrice(),
            ]],
        ]);

        $response = self::$client->getResponse();
        $this->assertEquals(422, $response->getStatusCode());
        $content = $response->getContent();
        $result = json_decode($content, true);
        $this->assertArrayHasKey('message', $result, join(',', array_keys($result)));
        $this->assertEquals('Krediit on otsas, su limiit on -25. Sinu uus krediit oleks olnud -25.2', $result['message']);
    }

}
