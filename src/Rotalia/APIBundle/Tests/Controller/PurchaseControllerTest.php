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
}
