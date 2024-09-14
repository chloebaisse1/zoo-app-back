<?php

namespace App\DataFixtures;

use App\Entity\User;
use DateTimeImmutable;
use Faker;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const USER_NB_TUPLES = 10;

    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create();
        for($i = 1; $i <= self::USER_NB_TUPLES; $i++){
            $user = (new User())
            ->setFirstName($faker->firstName())
            ->setLastName($faker->lastName())
            ->setEmail("email.$i@studi.fr")
            ->setCreatedAt(new DateTimeImmutable());

            $user ->setPassword($this->passwordHasher->hashPassword($user, "password$i"));
            $manager->persist($user);

        }
        $manager->flush();
    }
}
