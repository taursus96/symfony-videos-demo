<?php

namespace Tests\AppBundle;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Entity\User;
use AppBundle\Entity\Movie;

use AppBundle\Interfaces\IVote;

class MovieAccessTest extends TestCaseBase
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

    public function testCannotGiveAccessToFreeMovie()
    {
        /** @var Client $client */
        $client = self::createClient();
        $userTest2 = $this->getUser('test2');
        $this->loginUser('test', $client);
        $crawler = $client->request('GET', '/movie/give_access/' . $this->movieFree->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-give-access')->form([
            'movie_access[user]' => $userTest2->getUsername()
        ]);

        $crawler = $client->submit($form);

        $this->assertTrue($this->em->getRepository('AppBundle:MovieAccess')->findOneBy(['user' => $userTest2, 'movie' => $this->movieFree]) === null);
    }

    public function testCanGiveAccessToPrivateMovie()
    {
        /** @var Client $client */
        $client = self::createClient();
        $userTest2 = $this->getUser('test2');
        $this->loginUser('test', $client);
        $crawler = $client->request('GET', '/movie/give_access/' . $this->moviePrivate->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-give-access')->form([
            'movie_access[user]' => $userTest2->getUsername()
        ]);

        $crawler = $client->submit($form);

        $this->assertTrue($this->em->getRepository('AppBundle:MovieAccess')->findOneBy(['user' => $userTest2, 'movie' => $this->moviePrivate]) !== null);
    }

    public function testCanGiveAccessToPaidMovie()
    {
        /** @var Client $client */
        $client = self::createClient();
        $userTest2 = $this->getUser('test2');
        $this->loginUser('test', $client);
        $crawler = $client->request('GET', '/movie/give_access/' . $this->moviePaid->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-give-access')->form([
            'movie_access[user]' => $userTest2->getUsername()
        ]);

        $crawler = $client->submit($form);

        $this->assertTrue($this->em->getRepository('AppBundle:MovieAccess')->findOneBy(['user' => $userTest2, 'movie' => $this->moviePaid]) !== null);
    }

    public function testCannotGiveAccessToMovieThatUserDoesntOwn()
    {
        /** @var Client $client */
        $client = self::createClient();
        $userWithNoAccessToPrivateAndPaidMovies = $this->getUser('test-with-no-access-to-private-and-paid-movies');
        $this->loginUser($userWithNoAccessToPrivateAndPaidMovies->getUsername(), $client);
        $client->request('GET', '/movie/give_access/' . $this->moviePrivate->getId());

        $this->assertFalse($client->getResponse()->isSuccessful());
    }
}
