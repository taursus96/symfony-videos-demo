<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieView;
use AppBundle\Entity\User;

class MovieViewsCounterService
{
    /** @var EntityManager $em */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function countView(User $user = null, Movie $movie): bool
    {
        if (!$user) {
            return false;
        }

        $movieView = $this->em->getRepository('AppBundle:MovieView')->findOneBy(['movie' => $movie, 'user' => $user]);

        //Count only one view per day per user and use the same movieView entity because we only need to know how long ago view happened
        if ($movieView) {
            if (!$this->shouldViewBeCounted($movieView)) {
                return false;
            }
        } else {
            $movieView = $this->createMovieViewEntity($user, $movie);
        }

        $movieView->setViewedAt(new \DateTime('now'));
        $movie->setViews($movie->getViews() + 1);

        return true;
    }

    public function createMovieViewEntity(User $user, Movie $movie): MovieView
    {
        $movieView = new MovieView();
        $movieView->setMovie($movie);
        $movieView->setUser($user);

        $this->em->persist($movieView);
        return $movieView;
    }

    public function shouldViewBeCounted(MovieView $lastView): bool
    {
        $daysPassedSinceLastView = (int) $lastView->getViewedAt()->diff(new \DateTime('now'))->format('%a');
        if ($daysPassedSinceLastView < 1) {
            return false;
        }

        return true;
    }
}
