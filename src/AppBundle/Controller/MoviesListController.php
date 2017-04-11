<?php

namespace AppBundle\Controller;

use AppBundle\Repository\MovieRepository;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MoviesListController extends Controller
{
    /**
     * @Route("/movies/list/{type}", name="movies_list")
     */
    public function moviesListAction(Request $request, string $type): Response
    {
        /** @var MovieRepository $movieRepositor */
        $movieRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Movie');
        $movies = [];

        switch ($type) {
            default:
            case 'free-top-voted':
                $movies = $movieRepository->getFreeTopVoted();
                break;
            case 'free-top-viewed':
                $movies = $movieRepository->getFreeTopViewed();
                break;
            case 'paid-top-voted':
                $movies = $movieRepository->getPaidTopVoted();
                break;
            case 'paid-top-viewed':
                $movies = $movieRepository->getPaidTopViewed();
                break;
        }

        return $this->render('movies/list.html.twig', [
            'movies' => $movies
        ]);
    }

    /**
     * @Route("/movies/search", name="movies_search")
     */
    public function moviesSearchAction(Request $request): Response
    {
        /** @var MovieRepository $movieRepositor */
        $movieRepository = $this->getDoctrine()->getManager()->getRepository('AppBundle:Movie');
        $movies = $movieRepository->search($request->request->get('search'));

        return $this->render('movies/list_full.html.twig', [
            'movies' => $movies
        ]);
    }

    /**
     * @Route("/my_movies", name="my_movies")
     * @Security("is_fully_authenticated()")
     */
    public function myMoviesAction(Request $request): Response
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $movies = $em->getRepository('AppBundle:Movie')->findByPublisher($this->getUser());

        return $this->render('movie/my_movies.html.twig', [
            'movies' => $movies
        ]);
    }
}
