<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AuthTest extends WebTestCase
{
    // product site tests
    public function testAnUserCanSeeProductSite()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product');
        $this->assertResponseIsSuccessful();
    }

    // testAnUnloggedUserCanSeeProducts
    public function testAnUnloggedUserCanSeeProducts()
    {
        $client = static::createClient();
        $client->request('GET', '/product');
        $this->assertResponseRedirects('/login');
    }

    // product opinions tests
    public function testAnUserCanSeeExistingProductReview()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product/opinion/view/1');
        $this->assertResponseIsSuccessful();
    }

    // testAnUserCanSeeNoExistingProductReview
    public function testAnUserCanSeeNoExistingProductReview()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product/opinion/view/test');
        $this->assertResponseRedirects('/product');
    }

    // testAnUnloggedUserCanSeeProductsReview
    public function testAnUnloggedUserCanSeeProductsReview()
    {
        $client = static::createClient();
        $client->request('GET', '/product/opinion/view/1');
        $this->assertResponseRedirects('/login');
    }

    // testAnUnloggedUserCanSeeNoExistingProductsReview
    public function testAnUnloggedUserCanSeeNoExistingProductsReview()
    {
        $client = static::createClient();
        $client->request('GET', '/product/opinion/view/test');
        $this->assertResponseRedirects('/login');
    }


    // testAnLoggedUserCanOpinionHisOwnProduct
    public function testAnLoggedUserCanOpinionHisOwnProduct()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product/opinion/1');
        $this->assertResponseRedirects('/product');
    }


    // testAnLoggedUserCanOpinionNotHisProduct
    public function testAnLoggedUserCanOpinionNotHisProduct()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product/opinion/2');
        $this->assertResponseIsSuccessful();
    }

    // product edit tests
    public function testAnUserCanEditExistingProductReview()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product/edit/1');
        $this->assertResponseIsSuccessful();
    }

    // testAnUserCanEditNoExistingProductReview
    public function testAnUserCanEditNoExistingProductReview()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product/edit/test');
        $this->assertResponseRedirects('/product');
    }

    // testAnUnloggedUserCanEditProductsReview
    public function testAnUnloggedUserCanEditProductsReview()
    {
        $client = static::createClient();
        $client->request('GET', '/product/edit/1');
        $this->assertResponseRedirects('/login');
    }

    // testAnUnloggedUserCanEditNoExistingProductsReview
    public function testAnUnloggedUserCanEditNoExistingProductsReview()
    {
        $client = static::createClient();
        $client->request('GET', '/product/edit/test');
        $this->assertResponseRedirects('/login');
    }

    // testAnLoggedUserCanEditHisOwnProduct
    public function testAnLoggedUserCanEditHisOwnProduct()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product/edit/1');
        $this->assertResponseIsSuccessful();
    }

    // testAnLoggedUserCanEditNotHisProduct
    public function testAnLoggedUserCanEditNotHisProduct()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/product/edit/2');
        $this->assertResponseRedirects('/product');
    }

    // logout
    public function testAnLoggedUserCanLogout()
    {
        $client = static::createClient();
        $userRepository = static::getContainer()->get(UserRepository::class);

        $client->loginUser($userRepository->findOneByEmail('smth@op.pl'));

        $client->request('GET', '/logout');

        $this->assertResponseRedirects();
    }

    // testAnUnLoggedUserCanLogout
    public function testAnUnLoggedUserCanLogout()
    {
        $client = static::createClient();

        $client->request('GET', '/logout');

        $this->assertResponseRedirects();
    }
}
