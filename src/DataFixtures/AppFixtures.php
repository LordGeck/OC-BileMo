<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;
use Symfony\Component\String\Slugger\SluggerInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $encoder,
        private SluggerInterface $slugger,
    ) {}

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $client1 = new Client();
        $client1->setUsername('ClientDemo')
            ->setPassword($this->encoder->hashPassword($client1, 'password'));
        $manager->persist($client1);
        $client2 = new Client();
        $client2->setUsername('ClientDemo2')
            ->setPassword($this->encoder->hashPassword($client2, 'password'));
        $manager->persist($client2);

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setUsername($faker->unique()->userName())
                ->setSlug($this->slugger->slug($user->getUsername()))
                ->setEmail($faker->unique()->email())
                ->setClient($client1);
            $manager->persist($user);
        }

        for ($i = 0; $i < 20; $i++) {
            $user = new User();
            $user->setUsername($faker->unique()->userName())
                ->setSlug($this->slugger->slug($user->getUsername()))
                ->setEmail($faker->unique()->email())
                ->setClient($client2);
            $manager->persist($user);
        }

        for ($i = 0; $i < 40; $i++) {
            $product = new Product();
            $product->setName($faker->unique()->word())
                ->setSlug($this->slugger->slug($product->getName()))
                ->setPrice($faker->randomFloat(2, 50, 1000))
                ->setDescription($faker->text(250));
            $manager->persist($product);
        }

        $manager->flush();
    }
}
