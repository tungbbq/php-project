<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('max@muster.de');
        $user->setName('Max Muster');
        $user->setPlz(12345);
        $user->setOrt('Berlin');
        $user->setTelefon('01234-561789');
        $user->setPassword('111111');
        $manager->persist($user);

        $user2 = new User();
        $user2->setEmail('sarah@muster.de');
        $user2->setName('Sarah Muster');
        $user2->setPlz(12345);
        $user2->setOrt('Berlin');
        $user2->setTelefon('01234-561789');
        $user2->setPassword('111111');
        $manager->persist($user2);

        $manager->flush();
    }
}
