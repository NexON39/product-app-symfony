<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductControllerFormsTest extends WebTestCase
{
    // testAddNewProductByTestUser
    public function testAddNewProductByTestUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $crawler = $client->request('GET', '/product');

        $form = $crawler->selectButton('Add')->form();

        $form['product_add[productName]'] = 'TestProduct';

        $client->submit($form);

        $crawler = $client->request('GET', '/product');

        $this->assertTrue($crawler->filter('html:contains("Twój produkt został dodany")')->count() > 0);
    }

    // testEditProductByTestUser
    public function testEditProductByTestUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $crawler = $client->request('GET', '/product/edit/1');

        $form = $crawler->selectButton('Edit')->form();

        $form['product_edit[productName]'] = 'TestProduct';
        $form['product_edit[price]'] = 555;

        $client->submit($form);

        $crawler = $client->request('GET', '/product');

        $this->assertTrue($crawler->filter('html:contains("Twój produkt został edytowany")')->count() > 0);
    }

    // testReviewProductByTestUser
    public function testReviewProductByTestUser(): void
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $crawler = $client->request('GET', '/product/opinion/2');

        $form = $crawler->selectButton('Add')->form();

        $form['product_opinion[opinions]'] = 'TestProductOpinion';

        $client->submit($form);

        $crawler = $client->request('GET', '/product');

        $this->assertTrue($crawler->filter('html:contains("Pomyślnie dodano recenzję dla tego produktu")')->count() > 0);
    }
}
