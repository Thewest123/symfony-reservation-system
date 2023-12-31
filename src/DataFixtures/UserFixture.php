<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct (UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();

        $user = new User();
        $user->setUsername('admin');
        $user->setPassword(
            $this->hasher->hashPassword($user, '1234')
        );
        $user->setRoles(['ROLE_ADMIN']);
        $user->setName("admin");

        $manager->persist($user);
        $manager->flush();
    }
}
