<?php

namespace Tests\AppBundle;

use Symfony\Component\BrowserKit\Client;

use AppBundle\Entity\User;

class LoginTest extends TestCaseBase
{
    public function testIfLoginPageIsSuccessfulWithoutAuthentication()
    {
        /** @var Client $client */
        $client = self::createClient();
        $client->request('GET', '/login');
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testCanLoginWithValidData()
    {
        /** @var Client $client */
        $client = self::createClient();

        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/login');
        /** @var Form $form */
        $form = $crawler->filter('.form-login')->form([
            '_username' => 'test',
            '_password' => 'test'
        ]);

        $crawler = $client->submit($form);

        $this->assertRegExp('/\/$/', $client->getResponse()->headers->get('location'));
    }

    public function testCanLoginWithInvalidData()
    {
        /** @var Client $client */
        $client = self::createClient();

        /** @var Crawler $crawler */
        $crawler = $client->request('GET', '/login');

        /** @var Form $form */
        $form = $crawler->filter('.form-login')->form([
            '_username' => 'test',
            '_password' => 'invalid password'
        ]);

        $crawler = $client->submit($form);

        $this->assertNotRegExp('/\/$/', $client->getResponse()->headers->get('location'));
    }
}
