<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;

class JWTCreatedListener
{
    /**
     * Replaces the data in the generated
     *
     * @param JWTCreatedEvent $event
     *
     * @return void
     */
    public function onJWTCreated(JWTCreatedEvent $event)
    {
        /** @var $user Entity\User */
        $user = $event->getUser();

        // add new data
        $payload['Id'] = $user->getId();
        $payload['username'] = $user->getEmail();
        $payload['roles'] = $user->getRoles();
        $payload['verifyCode'] = $user->getVerifyCode();

        $event->setData($payload);
    }
}