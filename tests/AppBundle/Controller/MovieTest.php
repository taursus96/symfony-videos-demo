<?php

namespace Tests\AppBundle;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Component\Form\Form;

use AppBundle\Entity\User;
use AppBundle\Entity\Movie;

use AppBundle\Interfaces\IVote;

class MovieTest extends TestCaseBase
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

    public function getAdminUser(): User
    {
        return $this->em->getRepository('AppBundle:User')->findOneByUsername('admin');
    }

    public function testIfFreeMovieIsAccesibleWithoutAuthentication()
    {
        /** @var Client $client */
        $client = self::createClient();
        $client->request('GET', '/movie/' . $this->movieFree->getId());
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testIfPrivateMovieIsNotAccesibleWithoutAuthentication()
    {
        /** @var Client $client */
        $client = self::createClient();
        $client->request('GET', '/movie/' . $this->moviePrivate->getId());
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testIfPaidMovieIsNotAccesibleWithoutAuthentication()
    {
        /** @var Client $client */
        $client = self::createClient();
        $client->request('GET', '/movie/' . $this->moviePaid->getId());
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testIfFreeMovieIsAccesibleWithAuthentication()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test-with-no-access-to-private-and-paid-movies', $client);
        $client->request('GET', '/movie/' . $this->movieFree->getId());
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testIfPrivateMovieIsNotAccesibleWithAuthenticationWithoutAccess()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test-with-no-access-to-private-and-paid-movies', $client);
        $client->request('GET', '/movie/' . $this->moviePrivate->getId());
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testIfPaidMovieIsNotAccesibleWithutAuthenticationAndWithoutAccess()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test-with-no-access-to-private-and-paid-movies', $client);
        $client->request('GET', '/movie/' . $this->moviePaid->getId());
        $this->assertFalse($client->getResponse()->isSuccessful());
    }

    public function testIfPrivateMovieIsAccessibleByPublisher()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser($this->moviePrivate->getPublisher()->getUsername(), $client);
        $client->request('GET', '/movie/' . $this->moviePrivate->getId());
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testIfPaidMovieIsAccessibleByPublisher()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser($this->moviePaid->getPublisher()->getUsername(), $client);
        $client->request('GET', '/movie/' . $this->moviePaid->getId());
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testIfPrivateMovieIsAccessibleByAdmin()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser($this->getAdminUser()->getUsername(), $client);
        $client->request('GET', '/movie/' . $this->moviePrivate->getId());
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testIfPaidMovieIsAccessibleByAdmin()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser($this->getAdminUser()->getUsername(), $client);
        $client->request('GET', '/movie/' . $this->moviePaid->getId());
        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function getValidMovieFile()
    {
        return new UploadedFile(
            $this->getContainer()->getParameter('tests_resources_directory') . 'test.mp4',
            'test.mp4',
            'video/mp4'
        );
    }

    public function getInvalidMovieFile()
    {
        return $this->getValidPreviewFile();
    }

    public function getValidPreviewFile()
    {
        return new UploadedFile(
            $this->getContainer()->getParameter('tests_resources_directory') . 'preview.png',
            'preview.png',
            'image/png'
        );
    }

    public function getInvalidPreviewFile()
    {
        return $this->getValidMovieFile();
    }

    public function getValidDataForUploadForm()
    {
        return [
            'movie[title]' => uniqid(),
            'movie[description]' => uniqid(),
            'movie[access]' => Movie::ACCESS_FREE,
            'movie[file]' => $this->getValidMovieFile(),
            'movie[preview]' => $this->getValidPreviewFile()
        ];
    }

    public function testCanUploadMovieWithValidData()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/upload');

        $data = $this->getValidDataForUploadForm();

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-upload')->form($data);
        $crawler = $client->submit($form);

        $this->assertTrue($this->em->getRepository('AppBundle:Movie')->findOneByTitle($data['movie[title]']) !== null);
    }

    public function testCannotUploadMovieWithBlankTitle()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/upload');

        $data = $this->getValidDataForUploadForm();
        $data['movie[title]'] = '';

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-upload')->form($data);
        $crawler = $client->submit($form);

        $this->assertFalse($this->em->getRepository('AppBundle:Movie')->findOneByDescription($data['movie[description]']) !== null);
    }

    public function testCannotUploadMovieWithBlankDescription()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/upload');

        $data = $this->getValidDataForUploadForm();
        $data['movie[description]'] = '';

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-upload')->form($data);
        $crawler = $client->submit($form);

        $this->assertFalse($this->em->getRepository('AppBundle:Movie')->findOneByTitle($data['movie[title]']) !== null);
    }

    public function testCannotUploadMovieWithBlankFile()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/upload');

        $data = $this->getValidDataForUploadForm();
        $data['movie[file]'] = '';

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-upload')->form($data);
        $crawler = $client->submit($form);

        $this->assertFalse($this->em->getRepository('AppBundle:Movie')->findOneByTitle($data['movie[title]']) !== null);
    }

    public function testCannotUploadMovieWithMovieFileWithInvalidMimeType()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/upload');

        $data = $this->getValidDataForUploadForm();
        $data['movie[file]'] = $this->getInvalidMovieFile();

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-upload')->form($data);
        $crawler = $client->submit($form);

        $this->assertFalse($this->em->getRepository('AppBundle:Movie')->findOneByTitle($data['movie[title]']) !== null);
    }

    public function testCannotUploadMovieWithPreviewFileWithInvalidMimeType()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/upload');

        $data = $this->getValidDataForUploadForm();
        $data['movie[preview]'] = $this->getInvalidPreviewFile();

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-upload')->form($data);
        $crawler = $client->submit($form);

        $this->assertFalse($this->em->getRepository('AppBundle:Movie')->findOneByTitle($data['movie[title]']) !== null);
    }

    public function testIfVoteCountingWorksCorrectly()
    {
        /** @var Client $client */
        $client = self::createClient();
        $user = $this->loginUser('test', $client);

        //Remove existing vote
        $votes = $this->em->getRepository('AppBundle:MovieVote')->findBy(['voter' => $user]);
        foreach ($votes as $vote) {
            $this->em->remove($vote);
        }

        $this->em->flush();
        $this->em->refresh($this->movieFree);

        $thumbsUp = $this->movieFree->getThumbsUp();
        $thumbsDown = $this->movieFree->getThumbsDown();

        $client->request('GET', sprintf('/movie/vote/%d/%d', $this->movieFree->getId(), IVote::THUMBS_UP));
        $this->em->refresh($this->movieFree);
        $this->assertTrue($this->movieFree->getThumbsUp() === $thumbsUp + 1);
        $this->assertTrue($this->movieFree->getThumbsDown() === $thumbsDown);

        $thumbsUp = $this->movieFree->getThumbsUp();
        $thumbsDown = $this->movieFree->getThumbsDown();

        $client->request('GET', sprintf('/movie/vote/%d/%d', $this->movieFree->getId(), IVote::THUMBS_DOWN));
        $this->em->refresh($this->movieFree);
        $this->assertTrue($this->movieFree->getThumbsUp() === $thumbsUp - 1);
        $this->assertTrue($this->movieFree->getThumbsDown() === $thumbsDown + 1);

        $thumbsUp = $this->movieFree->getThumbsUp();
        $thumbsDown = $this->movieFree->getThumbsDown();

        $client->request('GET', sprintf('/movie/vote/%d/%d', $this->movieFree->getId(), IVote::THUMBS_UP));
        $this->em->refresh($this->movieFree);
        $this->assertTrue($this->movieFree->getThumbsUp() === $thumbsUp + 1);
        $this->assertTrue($this->movieFree->getThumbsDown() === $thumbsDown - 1);
    }

    public function testMyMoviesShowsAllMovies()
    {
        /** @var Client $client */
        $client = self::createClient();
        $user = $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/my_movies');

        $movies = $this->em->getRepository('AppBundle:Movie')->findByPublisher($user);

        $this->assertEquals(count($movies), $crawler->filter('.my-movies-movie')->count());
    }

    public function testCanEditMovieWithValidData()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/settings/' . $this->movieFree->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-settings')->form([
            'movie[description]' => $description = uniqid()
        ]);
        $crawler = $client->submit($form);

        $this->em->refresh($this->movieFree);

        $this->assertEquals($description, $this->movieFree->getDescription());
    }

    public function testCannotEditMovieWithBlankTitle()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/settings/' . $this->movieFree->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-settings')->form([
            'movie[title]' => $title = '',
            'movie[description]' => $description = uniqid()
        ]);
        $crawler = $client->submit($form);

        $this->em->refresh($this->movieFree);

        $this->assertNotEquals($title, $this->movieFree->getTitle());
        $this->assertNotEquals($description, $this->movieFree->getDescription());
    }

    public function testCannotEditMovieWithBlankDescription()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/settings/' . $this->movieFree->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-settings')->form([
            'movie[title]' => $title = uniqid(),
            'movie[description]' => $description = ''
        ]);
        $crawler = $client->submit($form);

        $this->em->refresh($this->movieFree);

        $this->assertNotEquals($title, $this->movieFree->getTitle());
        $this->assertNotEquals($description, $this->movieFree->getDescription());
    }

    public function testCannotEditMovieWithNegativePrice()
    {
        /** @var Client $client */
        $client = self::createClient();
        $this->loginUser('test', $client);

        $crawler = $client->request('GET', '/movie/settings/' . $this->moviePaid->getId());

        /** @var Form $form */
        $form = $crawler->filter('.form-movie-settings')->form([
            'movie[price]' => $price = -10
        ]);
        $crawler = $client->submit($form);

        $this->em->refresh($this->movieFree);

        $this->assertNotEquals($price, $this->moviePaid->getPrice());
    }
}
