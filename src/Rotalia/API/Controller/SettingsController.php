<?php

namespace Rotalia\API\Controller;

use App\Entity\Setting;
use App\Repository\ConventRepository;
use App\Repository\SettingRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

class SettingsController extends DefaultController
{
    /**
     * @throws ContainerExceptionInterface
     * @throws \Throwable
     * @throws NotFoundExceptionInterface
     */
    #[Route('/settings')]
    public function list(
        ConventRepository $conventRepository,
        SettingRepository $settingRepository,
    ): JsonResponse
    {
        // Find all convents which are active for Kassa
        $convents = $conventRepository->getActiveConvents();
        // Find all settings in 1 query
        $conventSettings = $settingRepository->findByObject(Setting::OBJECT_CONVENT);

        // Index settings by conventId
        $settings = [];
        foreach ($conventSettings as $setting) {
            $settings[$setting->getObjectId()][] = $setting;
        }

        foreach ($convents as $convent) {
            $convent->setSettings($settings[$convent->getId()] ?? []);
        }

        return $this->json([
            'activeConvents' => $convents,
        ]);
    }
}
