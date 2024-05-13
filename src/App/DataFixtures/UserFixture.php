<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class UserFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // TODO: Member

        // Super admin
        $user = new User();
        $user
            ->setUsername('user1')
            ->setPassword('test123')
            ->setPlugin(User::PLUGIN_PLAIN)
            ->setLiikmedId(123) // TODO: member.id
            ->setLastlogin(new \DateTime('2015-04-09 20:13:15'))
            ->setJutukasFirstmess(8765)
            ->setJutukasLastaccess(0);
        $manager->persist($user);

        // Admin
        $user = new User();
        $user
            ->setUsername('user2')
            ->setPassword('test123')
            ->setPlugin(User::PLUGIN_PLAIN)
            ->setLiikmedId(1234) // TODO: member.id
            ->setLastlogin(new \DateTime('2015-04-01 14:59:04'))
            ->setJutukasLastaccess(0);
        $manager->persist($user);

        // Member
        $user = new User();
        $user
            ->setUsername('user3')
            ->setPassword('*676243218923905CF94CB52A3C9D3EB30CE8E20D') // test123
            ->setPlugin(User::PLUGIN_NATIVE_PASSWORD)
            ->setLiikmedId(12345) // TODO: member.id
            ->setLastlogin(new \DateTime('2015-04-01 14:59:04'))
            ->setJutukasLastaccess(0);
        $manager->persist($user);

        // TODO: UserRight

        $manager->flush();
    }
}
