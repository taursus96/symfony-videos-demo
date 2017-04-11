<?php

namespace Tests\AppBundle;

use Symfony\Component\BrowserKit\Client;
use Symfony\Component\BrowserKit\Cookie;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Entity\User;
use AppBundle\Entity\Movie;

use AppBundle\Interfaces\IVote;

use AppBundle\Repository\MovieRepository;

class MoviesListTest extends TestCaseBase
{
    /**
     * @dataProvider listDataProvider
     */
    public function testList($data)
    {
        /** @var Client $client */
        $client = self::createClient();
        $crawler = $client->request('GET', '/movies/list/' . $data['type']);

        $firstMovieTitle = $crawler->first()->filter('.movies-list-movie-title')->text();

        $shouldBeFirstMovie = $this->em
            ->createQuery('SELECT m FROM AppBundle:Movie m WHERE m.access = :access ORDER BY m.'. $data['orderBy'] . ' DESC')
            ->setParameter(':access', $data['access'])
            ->setMaxResults(1)
            ->getSingleResult();

        $this->assertEquals($firstMovieTitle, $shouldBeFirstMovie->getTitle());
    }

    public function listDataProvider()
    {
        yield [['type' => 'free-top-voted', 'access' => Movie::ACCESS_FREE, 'orderBy' => 'thumbsUp']];
        yield [['type' => 'free-top-viewed', 'access' => Movie::ACCESS_FREE, 'orderBy' => 'views']];
        yield [['type' => 'paid-top-voted', 'access' => Movie::ACCESS_PAID, 'orderBy' => 'thumbsUp']];
        yield [['type' => 'paid-top-viewed', 'access' => Movie::ACCESS_PAID, 'orderBy' => 'views']];
    }

    /**
     * @dataProvider searchDataProvider
     */
    public function testSearch($data)
    {
        /** @var Client $client */
        $client = self::createClient();
        $crawler = $client->request('GET', '/');

        $form = $crawler->filter('.form-search')->form([
            'search' => $data['search']
        ]);
        $crawler = $client->submit($form);

        $moviesSearchCount = $crawler->first()->filter('.movies-list-movie-title')->count();

        $shouldBeMoviesSearchCount = $this->em
            ->createQuery('SELECT count(m.id) FROM AppBundle:Movie m WHERE m.access != :privateAccess AND m.title LIKE :search')
            ->setParameter(':privateAccess', Movie::ACCESS_PRIVATE)
            ->setParameter(':search', '%' . $data['search'] . '%')
            ->getSingleScalarResult();

        $this->assertEquals($moviesSearchCount, $shouldBeMoviesSearchCount);
    }

    public function searchDataProvider()
    {
        yield [['search' => 's']];
        yield [['search' => 'free']];
        yield [['search' => 'private']];
        yield [['search' => 'fsfad']];
    }
}
