<?php

namespace Tests\AppBundle;

use Symfony\Component\BrowserKit\Client;

use Symfony\Component\DomCrawler\Crawler;

use Symfony\Component\Form\Form;

use AppBundle\Entity\User;

class RegistrationTest extends TestCaseBase
{
    public function testIfRegistrationPageIsSuccessfulWithoutAuthentication()
    {
        /** @var Client $client */
        $client = self::createClient();
        $client->request('GET', '/registration');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testCanRegisterWithValidData()
    {
        /** @var Client $client */
        $client = self::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/registration');

        /** @var Form $form */
        $form = $crawler->filter('.form-registration')->form([
            'user[username]' => uniqid(),
            'user[email]' => uniqid() . '@sd.sd',
            'user[plainPassword][first]' => 'testX',
            'user[plainPassword][second]' => 'testX',
        ]);

        $crawler = $client->submit($form);
        //Redirect so registration succeeded
        $this->assertRegExp('/\/login$/', $client->getResponse()->headers->get('location'));
    }

    public function testCannotRegisterWithAlreadyUsedUsername()
    {
        /** @var Client $client */
        $client = self::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/registration');

        /** @var Form $form */
        $form = $crawler->filter('.form-registration')->form([
            'user[username]' => 'test',
            'user[email]' => uniqid() . '@sd.sd',
            'user[plainPassword][first]' => 'testX',
            'user[plainPassword][second]' => 'testX',
        ]);

        $crawler = $client->submit($form);
        //No redirect so registration failed
        $this->assertNull($client->getResponse()->headers->get('location'));
    }

    public function testCannotRegisterWithAlreadyUsedEmail()
    {
        /** @var Client $client */
        $client = self::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/registration');

        /** @var Form $form */
        $form = $crawler->filter('.form-registration')->form([
            'user[username]' => uniqid(),
            'user[email]' => 'test@test.test',
            'user[plainPassword][first]' => 'testX',
            'user[plainPassword][second]' => 'testX',
        ]);

        $crawler = $client->submit($form);
        //No redirect so registration failed
        $this->assertNull($client->getResponse()->headers->get('location'));
    }

    public function testCannotRegisterWithNotIdenticalPasswords()
    {
        /** @var Client $client */
        $client = self::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/registration');

        /** @var Form $form */
        $form = $crawler->filter('.form-registration')->form([
            'user[username]' => 'testX4',
            'user[email]' => 'testX4@test.test',
            'user[plainPassword][first]' => 'testX4',
            'user[plainPassword][second]' => 'testX',
        ]);

        $crawler = $client->submit($form);
        //No redirect so registration failed
        $this->assertNull($client->getResponse()->headers->get('location'));
    }

    public function testCannotRegisterWithInvalidUsername()
    {
        /** @var Client $client */
        $client = self::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/registration');

        /** @var Form $form */
        $form = $crawler->filter('.form-registration')->form([
            'user[username]' => '',
            'user[email]' => uniqid() . '@test.test',
            'user[plainPassword][first]' => 'testX4',
            'user[plainPassword][second]' => 'testX',
        ]);

        $crawler = $client->submit($form);
        //No redirect so registration failed
        $this->assertNull($client->getResponse()->headers->get('location'));
    }

    public function testCannotRegisterWithInvalidEmail()
    {
        /** @var Client $client */
        $client = self::createClient();
        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/registration');

        /** @var Form $form */
        $form = $crawler->filter('.form-registration')->form([
            'user[username]' => uniqid(),
            'user[email]' => '',
            'user[plainPassword][first]' => 'testX4',
            'user[plainPassword][second]' => 'testX',
        ]);

        $crawler = $client->submit($form);
        //No redirect so registration failed
        $this->assertNull($client->getResponse()->headers->get('location'));
    }
}
