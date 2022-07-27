<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // add first test user
        $user = new User;
        $user->setEmail('admin@gmail.com');
        $user->setPassword('$2y$13$aICB.DrRQhVgbV0a3Bf3ROKLPgtZtFQLDV0i7NeJJ1.zTcbuv0yQ6');
        $manager->persist($user);
        $manager->flush();

        // add second test user
        $user = new User;
        $user->setEmail('smth@op.pl');
        $user->setPassword('$2y$13$OrVkLLXCdEM56ScUggyoK.H92YScZB77qX7ogTyS/B7GBj/3Lgy22');
        $manager->persist($user);
        $manager->flush();

        // add first test product
        $product = new Product;
        $product->setOwnerName('smth@op.pl');
        $product->setProductName('car');
        $product->setPrice(10);
        $manager->persist($product);
        $manager->flush();

        // add secont test product
        $product = new Product;
        $product->setOwnerName('admin@gmail.com');
        $product->setProductName('telephones');
        $product->setPrice(20);
        $manager->persist($product);
        $manager->flush();
    }
}
