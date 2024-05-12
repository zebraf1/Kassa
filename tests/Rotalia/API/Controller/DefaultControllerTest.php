<?php

namespace Tests\Rotalia\API\Controller;

use Exception;
use JsonSerializable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Rotalia\API\Controller\DefaultController;
use Rotalia\APIBundle\Component\HttpFoundation\JSendResponse;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Tests\Helpers\ControllerTestCase;
use Throwable;

interface DummyController
{
    public function test(int $status): JsonResponse;
}

#[CoversClass(DefaultController::class)]
#[UsesClass(JSendResponse::class)]
class DefaultControllerTest extends ControllerTestCase
{
    private function getDummyController(): DummyController
    {
        $mockContainer = $this->getMockBuilder(ServiceLocator::class)->disableOriginalConstructor()
            ->onlyMethods(['has'])
            ->getMock();
        $mockContainer->method('has')->willReturn(false);

        $controller = new class extends DefaultController implements DummyController {
            protected function getParameter(string $name): string
            {
                return false; // debug
            }

            public function test(int $status): JsonResponse
            {
                return $this->json(['test'], $status);
            }
        };

        $controller->setContainer($mockContainer);

        return $controller;
    }

    public function testJsonSuccess(): void
    {
        $response = $this->getDummyController()->test(200);
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => JSendResponse::JSEND_STATUS_SUCCESS,
            'data' => [
                'test',
            ],
        ]), $response->getContent());
    }

    public function testJsonError(): void
    {
        $response = $this->getDummyController()->test(400);
        $this->assertJsonStringEqualsJsonString(json_encode([
            'test',
        ]), $response->getContent());
    }

    /**
     * @throws Throwable
     */
    public function testGetJSendResponseSuccess(): void
    {
        $jsonData = [
            'dummy' => [
                'key' => [3, 4],
            ]
        ];
        $headers = ['header1' => 'value1'];

        $response = DefaultController::getJSendResponse($jsonData, $headers, 200, true);
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => JSendResponse::JSEND_STATUS_SUCCESS,
            'data' => $jsonData,
        ]), $response->getContent());
        $this->assertSame('value1', $response->headers->get('header1'));
    }

    /**
     * @throws Throwable
     */
    public function testGetJSendResponseError(): void
    {
        $errorObject = new class implements JsonSerializable {
            public function jsonSerialize(): array
            {
                throw new Exception('Test');
            }
        };

        $response = DefaultController::getJSendResponse($errorObject, ['h1' => 'v1'], 404);
        $this->assertJson($response->getContent());
        $this->assertJsonStringEqualsJsonString(json_encode([
            'status' => JSendResponse::JSEND_STATUS_ERROR,
            'message' => 'Süsteemi viga',
            'data' => [
                'error' => 'Test',
                'trace' => false,
            ],
        ]), $response->getContent());
        $this->assertSame('v1', $response->headers->get('h1'));
    }
}
