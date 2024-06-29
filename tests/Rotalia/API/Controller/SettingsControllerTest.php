<?php

namespace Tests\Rotalia\API\Controller;

use App\Entity\Convent;
use App\Entity\Setting;
use Hautelook\AliceBundle\PhpUnit\FixtureStore;
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

    public function testListSuccess(): void
    {
        $this->loginSimpleUser();

        /** @var Convent $tallinn */
        $tallinn = FixtureStore::getFixtures()['Convent_6'];
        /** @var Setting $setting1 */
        $setting1 = FixtureStore::getFixtures()['Setting_Tallinn_Cash'];
        /** @var Setting $setting2 */
        $setting2 = FixtureStore::getFixtures()['Setting_Tallinn_BankAccountOwner'];
        /** @var Setting $setting3 */
        $setting3 = FixtureStore::getFixtures()['Setting_Tallinn_BankAccountIban'];

        $client = self::$client;
        $client->request('GET', '/api/settings');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseEqualsJsonPath([
            'id' => $tallinn->getId(),
            'name' => $tallinn->getName(),
            'settings' => [
                Setting::REFERENCE_CURRENT_CASH => $setting1->value,
                Setting::REFERENCE_BANK_ACCOUNT_OWNER => $setting2->value,
                Setting::REFERENCE_BANK_ACCOUNT_IBAN => $setting3->value,
            ],
        ], 'data.activeConvents.0');
    }
}
