<?php

namespace Tests\Rotalia\API\Controller;

use App\Entity\Convent;
use App\Entity\Setting;
use App\Repository\ConventRepository;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Rotalia\API\Controller\SettingsController;
use Symfony\Component\HttpFoundation\Response;
use Tests\Helpers\ControllerTestCase;
use Tests\Helpers\EntityManagerAwareTestCase;

#[CoversClass(SettingsController::class)]
class SettingsControllerTest extends ControllerTestCase
{
    use EntityManagerAwareTestCase;
    use RefreshDatabaseTrait;

    public function testListUnauthorized(): void
    {
        $client = self::$client;
        $client->request('GET', '/api/settings');
        self::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    /**
     * @throws \JsonException
     */
    public function testListSuccess(): void
    {
        $this->loginSimpleUser();

        /** @var ConventRepository $conventRepository */
        $conventRepository = $this->entityManager->getRepository(Convent::class);
        $tallinn = $conventRepository->findOneBy(['name' => 'Tallinn']);
        $settingCash = (new Setting())
            ->setConvent($tallinn)
            ->setReference(Setting::REFERENCE_CURRENT_CASH)
            ->setValue('2.33')
        ;
        /** @see RefreshDatabaseTrait will perform rollback */
        $this->entityManager->persist($settingCash);
        $this->entityManager->flush();
        $convents = $conventRepository->getActiveConvents();

        // Check Tallinn is first in list
        $this->assertEquals($tallinn->getId(), $convents[0]->getId());

        $client = self::$client;
        $client->request('GET', '/api/settings');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseEqualsJsonPath([
            'id' => $tallinn->getId(),
            'name' => $tallinn->getName(),
            'settings' => [
                Setting::REFERENCE_CURRENT_CASH => '2.33',
            ],
        ], 'data.activeConvents.0');
    }
}
