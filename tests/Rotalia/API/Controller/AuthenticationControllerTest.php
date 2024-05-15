<?php

namespace Tests\Rotalia\API\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use Rotalia\API\Controller\AuthenticationController;
use Rotalia\API\Services\Security\AuthenticationFailureHandler;
use Rotalia\API\Services\Security\UserLastLoginUpdater;
use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\ControllerTestCase;

#[CoversClass(AuthenticationController::class)]
#[CoversClass(AuthenticationFailureHandler::class)]
#[CoversClass(UserLastLoginUpdater::class)]
class AuthenticationControllerTest extends ControllerTestCase
{
    /**
     * @throws \JsonException
     */
    public function testCheckUnauthorized(): void
    {
        $client = self::$client;
        $client->request('GET', '/api/authentication');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals([
            'status' => JSendResponse::JSEND_STATUS_SUCCESS,
            'data' => [
                'member' => null,
                'pointOfSaleId' => null,
            ],
        ], $json);
    }

    /**
     * @throws \JsonException
     */
    public function testCheckSuccess(): void
    {
        $user = $this->loginSimpleUser();

        $client = self::$client;
        $client->request('GET', '/api/authentication');
        $response = $client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $json = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals([
            'status' => JSendResponse::JSEND_STATUS_SUCCESS,
            'data' => [
                'member' => [
                    'id' => $user->getLiikmedId(),
                    'name' => $user->getUsername(),
                    'conventId' => null,
                    'creditBalance' => null,
                    'roles' => $user->getRoles(),
                ],
                'pointOfSaleId' => null,
            ],
        ], $json);
    }

    /**
     * @throws \JsonException
     */
    public function testJsonSuccess(): void
    {
        $client = self::$client;
        $client->jsonRequest('POST', '/api/authentication', [
            'username' => 'user1',
            'password' => 'test123',
        ]);
        $response = $client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
        $getResult = json_decode($response->getContent(), false, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals('success', $getResult->status);
        self::assertBrowserHasCookie('MOCKSESSID');
    }

    /**
     * @throws \JsonException
     */
    public function testJsonWrongPass(): void
    {
        $client = self::$client;
        $client->jsonRequest('POST', '/api/authentication', [
            'username' => 'user1',
            'password' => 'test1234',
        ]);
        $response = $client->getResponse();
        $this->assertEquals(401, $response->getStatusCode());
        $getResult = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertEquals([
            'message' => 'Vale kasutaja/parool',
            'status' => JSendResponse::JSEND_STATUS_FAIL,
        ], $getResult, $response->getContent());
    }
}
