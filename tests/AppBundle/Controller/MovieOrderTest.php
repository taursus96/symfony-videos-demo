<?php

namespace Tests\AppBundle;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Entity\User;
use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieOrder;

use AppBundle\Interfaces\IVote;

class MovieOrderTest extends TestCaseBase
{
    /** @var Movie $movieFree */
    protected $movieFree;
    /** @var Movie $moviePrivate */
    protected $moviePrivate;
    /** @var Movie $moviePaid */
    protected $moviePaid;

    public function setUp()
    {
        parent::setUp();
        $this->movieFree = $this->em->getRepository('AppBundle:Movie')->findOneByTitle('free');
        $this->moviePrivate = $this->em->getRepository('AppBundle:Movie')->findOneByTitle('private');
        $this->moviePaid = $this->em->getRepository('AppBundle:Movie')->findOneByTitle('paid');
    }

    public function testCannotOrderWithoutAuthentication()
    {
        /** @var Client $client */
        $client = self::createClient();
        $crawler = $client->request('GET', '/movie/order/' . $this->moviePaid->getId());
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testCannotOrderNotOrderableMovie()
    {
        /** @var Client $client */
        $client = self::createClient();
        $user = $this->loginUser('test2', $client);

        //Make sure user doesn't have access to movie
        $accesses = $this->em->getRepository('AppBundle:MovieAccess')->findByUser($user);
        foreach ($accesses as $access) {
            $this->em->remove($access);
        }
        $this->em->flush();

        $crawler = $client->request('GET', '/movie/order/' . $this->movieFree->getId());
        $this->assertFalse($client->getResponse()->isSuccessful());

        $crawler = $client->request('GET', '/movie/order/' . $this->moviePrivate->getId());
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testCannotOrderMovieThatUserAlreadyHasAccessTo()
    {
        /** @var Client $client */
        $client = self::createClient();
        $user = $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/order/' . $this->moviePrivate->getId());
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testCanOrder()
    {
        /** @var Client $client */
        $client = self::createClient();
        $user = $this->loginUser('test2', $client);

        $crawler = $client->request('GET', '/movie/order/' . $this->moviePaid->getId());
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testCanCompleteOrderWithValidData()
    {
        /** @var Client $client */
        $client = self::createClient([], ['REMOTE_ADDR' => $this->getContainer()->getParameter('dotpay_accepted_ip_adresses')[0]]);
        $user = $this->loginUser('test2', $client);

        //Make sure user doesn't have access to movie
        $accesses = $this->em->getRepository('AppBundle:MovieAccess')->findByUser($user);
        foreach ($accesses as $access) {
            $this->em->remove($access);
        }
        $this->em->flush();

        $movieOrder = new MovieOrder();
        $movieOrder->setUser($user);
        $movieOrder->setPrice($this->moviePaid->getPrice());
        $movieOrder->setMovie($this->moviePaid);
        $this->em->persist($movieOrder);
        $this->em->flush();

        $crawler = $client->request('POST', '/movie/complete_order', [
            'control' => $movieOrder->getId(),
            'operation_amount' => $operationAmount = $this->moviePaid->getPrice() / 100,
            'signature' => hash('sha256', $this->getContainer()->getParameter('dotpay_pin') . $operationAmount . $movieOrder->getId())
        ]);

        $this->assertTrue($client->getResponse()->getContent() === 'OK');
        $this->assertTrue($this->em->getRepository('AppBundle:MovieAccess')->findOneBy(['user' => $user, 'movie' => $this->moviePaid]) !== null);
    }

    public function testCannotCompleteOrderWithInvalidIpAdress()
    {
        /** @var Client $client */
        $client = self::createClient([], ['REMOTE_ADDR' => '1.1.1.1']);
        $user = $this->loginUser('test2', $client);

        //Make sure user doesn't have access to movie
        $accesses = $this->em->getRepository('AppBundle:MovieAccess')->findByUser($user);
        foreach ($accesses as $access) {
            $this->em->remove($access);
        }
        $this->em->flush();

        $movieOrder = new MovieOrder();
        $movieOrder->setUser($user);
        $movieOrder->setPrice($this->moviePaid->getPrice());
        $movieOrder->setMovie($this->moviePaid);
        $this->em->persist($movieOrder);
        $this->em->flush();

        $crawler = $client->request('POST', '/movie/complete_order', [
            'control' => $movieOrder->getId(),
            'operation_amount' => $operationAmount = $this->moviePaid->getPrice() / 100,
            'signature' => hash('sha256', $this->getContainer()->getParameter('dotpay_pin') . $operationAmount . $movieOrder->getId())
        ]);

        $this->assertFalse($client->getResponse()->getContent() === 'OK');
        $this->assertFalse($this->em->getRepository('AppBundle:MovieAccess')->findOneBy(['user' => $user, 'movie' => $this->moviePaid]) !== null);
    }

    public function testCannotCompleteOrderWithInvalidSignature()
    {
        /** @var Client $client */
        $client = self::createClient([], ['REMOTE_ADDR' => '1.1.1.1']);
        $user = $this->loginUser('test2', $client);

        //Make sure user doesn't have access to movie
        $accesses = $this->em->getRepository('AppBundle:MovieAccess')->findByUser($user);
        foreach ($accesses as $access) {
            $this->em->remove($access);
        }
        $this->em->flush();

        $movieOrder = new MovieOrder();
        $movieOrder->setUser($user);
        $movieOrder->setPrice($this->moviePaid->getPrice());
        $movieOrder->setMovie($this->moviePaid);
        $this->em->persist($movieOrder);
        $this->em->flush();

        $crawler = $client->request('POST', '/movie/complete_order', [
            'control' => $movieOrder->getId(),
            'operation_amount' => $operationAmount = $this->moviePaid->getPrice() / 100,
            'signature' => hash('sha256', 'invalid signature')
        ]);

        $this->assertFalse($client->getResponse()->getContent() === 'OK');
        $this->assertFalse($this->em->getRepository('AppBundle:MovieAccess')->findOneBy(['user' => $user, 'movie' => $this->moviePaid]) !== null);
    }

    public function testCannotCompleteOrderWithInvalidOperationAmount()
    {
        /** @var Client $client */
        $client = self::createClient([], ['REMOTE_ADDR' => $this->getContainer()->getParameter('dotpay_accepted_ip_adresses')[0]]);
        $user = $this->loginUser('test2', $client);

        //Make sure user doesn't have access to movie
        $accesses = $this->em->getRepository('AppBundle:MovieAccess')->findByUser($user);
        foreach ($accesses as $access) {
            $this->em->remove($access);
        }
        $this->em->flush();

        $movieOrder = new MovieOrder();
        $movieOrder->setUser($user);
        $movieOrder->setPrice($this->moviePaid->getPrice());
        $movieOrder->setMovie($this->moviePaid);
        $this->em->persist($movieOrder);
        $this->em->flush();

        $crawler = $client->request('POST', '/movie/complete_order', [
            'control' => $movieOrder->getId(),
            'operation_amount' => $operationAmount = $this->moviePaid->getPrice() / 100 - 1,
            'signature' => hash('sha256', $this->getContainer()->getParameter('dotpay_pin') . $operationAmount . $movieOrder->getId())
        ]);

        $this->assertFalse($client->getResponse()->getContent() === 'OK');
        $this->assertFalse($this->em->getRepository('AppBundle:MovieAccess')->findOneBy(['user' => $user, 'movie' => $this->moviePaid]) !== null);
    }
}
