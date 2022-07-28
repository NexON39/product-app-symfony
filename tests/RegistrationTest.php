<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\UserRepository;

class RegistrationTest extends WebTestCase
{
    // testRegistrationFrom
    public function testRegistrationFrom(): void
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/register');

        $form = $crawler->selectButton('Register')->form();
        $form['registration_form[email]'] = 'TestUser@test.pl';
        $form['registration_form[plainPassword]'] = 'TestPassword123123123';
        $form['registration_form[agreeTerms]']->tick();

        $client->submit($form);

        $this->assertResponseRedirects('/product');
    }
}
