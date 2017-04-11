<?php

namespace Tests\AppBundle;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Entity\User;
use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieComment;

use AppBundle\Interfaces\IVote;

class MovieCommentTest extends TestCaseBase
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

    public function testCannotCommentOnMovieWithoutAccess()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser($this->moviePrivate->getPublisher()->getUsername(), $client);

        $crawler = $client->request('GET', '/movie/' . $this->moviePrivate->getId());

        $this->loginUser('test-with-no-access-to-private-and-paid-movies', $client);

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-comment')->form([
            'movie_comment[content]' => $content = uniqid()
        ]);

        $crawler = $client->submit($form);

        $this->assertTrue($this->em->getRepository('AppBundle:MovieComment')->findOneByContent($content) === null);
    }

    public function testCanCommentOnMovieWithAccess()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser($this->moviePrivate->getPublisher()->getUsername(), $client);

        $crawler = $client->request('GET', '/movie/' . $this->moviePrivate->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-comment')->form([
            'movie_comment[content]' => $content = uniqid()
        ]);

        $crawler = $client->submit($form);

        $this->assertTrue($this->em->getRepository('AppBundle:MovieComment')->findOneByContent($content) !== null);
    }

    public function testCannotCommentOnMovieWithBlankContent()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser($this->moviePrivate->getPublisher()->getUsername(), $client);

        $crawler = $client->request('GET', '/movie/' . $this->moviePrivate->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-comment')->form([
            'movie_comment[content]' => $content = ''
        ]);

        $crawler = $client->submit($form);

        $this->assertTrue($this->em->getRepository('AppBundle:MovieComment')->findOneByContent($content) === null);
    }

    public function testIfVoteCountingWorksCorrectly()
    {
        /** @var Client $client */
        $client = self::createClient();
        $user = $this->loginUser('test', $client);

        //create comment
        $crawler = $client->request('GET', '/movie/' . $this->movieFree->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-comment')->form([
            'movie_comment[content]' => $content = uniqid()
        ]);

        $crawler = $client->submit($form);

        /** @var MovieComment $comment */
        $comment = $this->em->getRepository('AppBundle:MovieComment')->findOneByContent($content);

        $thumbsUp = $comment->getThumbsUp();
        $thumbsDown = $comment->getThumbsDown();

        $client->request('GET', sprintf('/movie/comment/vote/%d/%d', $comment->getId(), IVote::THUMBS_UP));
        $this->em->refresh($comment);
        $this->assertTrue($comment->getThumbsUp() === $thumbsUp + 1);
        $this->assertTrue($comment->getThumbsDown() === $thumbsDown);

        $thumbsUp = $comment->getThumbsUp();
        $thumbsDown = $comment->getThumbsDown();

        $client->request('GET', sprintf('/movie/comment/vote/%d/%d', $comment->getId(), IVote::THUMBS_DOWN));
        $this->em->refresh($comment);
        $this->assertTrue($comment->getThumbsUp() === $thumbsUp - 1);
        $this->assertTrue($comment->getThumbsDown() === $thumbsDown + 1);

        $thumbsUp = $comment->getThumbsUp();
        $thumbsDown = $comment->getThumbsDown();

        $client->request('GET', sprintf('/movie/comment/vote/%d/%d', $comment->getId(), IVote::THUMBS_UP));
        $this->em->refresh($comment);
        $this->assertTrue($comment->getThumbsUp() === $thumbsUp + 1);
        $this->assertTrue($comment->getThumbsDown() === $thumbsDown - 1);
    }

    public function testCannotVoteOnMovieCommentWithoutAuthentication()
    {
        /** @var Client $client */
        $client = self::createClient();
        $user = $this->loginUser('test', $client);
        //create comment
        $crawler = $client->request('GET', '/movie/' . $this->movieFree->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-comment')->form([
            'movie_comment[content]' => $content = uniqid()
        ]);

        $crawler = $client->submit($form);

        /** @var MovieComment $comment */
        $comment = $this->em->getRepository('AppBundle:MovieComment')->findOneByContent($content);

        /** @var Client $client */
        $client = self::createClient(); //create new client without authentication
        $client->request('GET', sprintf('/movie/comment/vote/%d/%d', $comment->getId(), IVote::THUMBS_DOWN));
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testCannotVoteOnMovieCommentWithoutAccess()
    {
        /** @var Client $client */
        $client = self::createClient();
        $user = $this->loginUser('test', $client);
        //create comment
        $crawler = $client->request('GET', '/movie/' . $this->moviePrivate->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-comment')->form([
            'movie_comment[content]' => $content = uniqid()
        ]);

        $crawler = $client->submit($form);

        /** @var MovieComment $comment */
        $comment = $this->em->getRepository('AppBundle:MovieComment')->findOneByContent($content);

        $this->loginUser('test-with-no-access-to-private-and-paid-movies', $client);
        $client->request('GET', sprintf('/movie/comment/vote/%d/%d', $comment->getId(), IVote::THUMBS_DOWN));
        $this->assertFalse($client->getResponse()->isSuccessful());
    }
}
