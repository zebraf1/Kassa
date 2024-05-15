<?php

namespace Rotalia\API\Services\Security;

use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;

#[AsEventListener(event: AuthenticationSuccessEvent::class, method: 'onAuthenticationSuccess')]
class UserLastLoginUpdater
{
    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->entityManager = $manager;
    }

    /**
     * @see AuthenticationEvents::AUTHENTICATION_SUCCESS
     * @param AuthenticationSuccessEvent $event
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        // Update last login time
        if ($user instanceof User) {
            $user->setLastLogin(new DateTime());
            $this->entityManager->persist($user);
        }
    }
}
