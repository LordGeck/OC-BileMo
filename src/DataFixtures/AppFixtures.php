<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Product;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $client = new Client();
        $client->setUsername('ClientDemo')
            ->setPassword($this->encoder->encodePassword($client, 'password'));
        $manager->persist($client);

        $client = new Client();
        $client->setUsername('ClientDemo2')
            ->setPassword($this->encoder->encodePassword($client, 'password'));
        $manager->persist($client);

        $product = new Product();
        $product->setName('ProductDemo')
            ->setPrice('149.99')
            ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent suscipit, tellus id imperdiet egestas, diam risus mattis metus, vitae lobortis ex libero et enim. Curabitur pretium libero dolor. Donec commodo diam eget justo efficitur, nec placerat neque auctor. Maecenas vestibulum nisl et lectus lacinia, eu mollis ligula congue. In finibus cursus finibus. Integer viverra at arcu vel pulvinar. Orci varius natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec dignissim nulla et lorem condimentum, eu cursus sapien tempor.');
        $manager->persist($product);

        $manager->flush();
    }
}
