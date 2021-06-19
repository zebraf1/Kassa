<?php

namespace Rotalia\UserBundle\Security;

use DateTime;
use PropelException;
use Rotalia\UserBundle\Model\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class AuthenticationListener
{
    protected $canMigratePassword;
    protected $encoderFactory;
    protected $eraseCredentials;

    public function __construct(EncoderFactory $encoderFactory, $canMigratePassword = false, bool $eraseCredentials = false)
    {
        $this->canMigratePassword = $canMigratePassword;
        $this->encoderFactory = $encoderFactory;
        $this->eraseCredentials = $eraseCredentials;
    }

    /**
     * @see AuthenticationEvents::AUTHENTICATION_SUCCESS
     * @param AuthenticationEvent $event
     * @throws PropelException
     */
    public function onAuthenticationSuccess(AuthenticationEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();

        if ($user instanceof User) {
            // Update last login time
            $user->setLastLogin(new DateTime());

            // If migration is enabled then convert password using native password plugin
            // AuthenticationManager eraseCredentials must be false so we get raw password
            if ($this->canMigratePassword === true
                && $user->getPlugin() !== RotaliaPasswordEncoder::PLUGIN_NATIVE_PASSWORD
                && !empty($token->getCredentials())
            ) {
                $user->setPlugin(RotaliaPasswordEncoder::PLUGIN_NATIVE_PASSWORD);
                $raw = $token->getCredentials();
                $encoded = $this->encoderFactory->getEncoder($user)->encodePassword($raw, $user->getSalt());
                $user->setPassword($encoded);
            }

            $user->save();
        }

        // To allow password migrations we must disable eraseCredentials for AuthenticationManager
        // This option allows us to ensure credentials are still erased
        if ($this->eraseCredentials === true) {
            $token->eraseCredentials();
        }
    }
}
