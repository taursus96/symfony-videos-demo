<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieAccess;
use AppBundle\Entity\User;

use AppBundle\Exception\AlreadyHasAccessException;

class MovieAccessService
{
    /** @var EntityManager $em */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function hasAccess(User $user = null, Movie $movie): bool
    {
        if ($movie->getAccess() === Movie::ACCESS_FREE || $movie->getPublisher() === $user || ($user && $user->hasRole('ROLE_ADMIN'))) {
            return true;
        }

        $movieAccess = $this->em->getRepository('AppBundle:MovieAccess')->findOneBy([
            'movie' => $movie,
            'user' => $user
        ]);

        return $movieAccess ? true : false;
    }

    public function canModify(User $user = null, Movie $movie): bool
    {
        return $user && ($movie->getPublisher() === $user || $user->hasRole('ROLE_ADMIN'));
    }

    public function giveAccess(User $user = null, Movie $movie, User $otherUser = null): bool
    {
        if (!$this->canModify($user, $movie) || !$otherUser) {
            return false;
        }

        return $this->createAccess($otherUser, $movie) ? true : false;
    }

    public function createAccess(User $user, Movie $movie): MovieAccess
    {
        if ($this->hasAccess($user, $movie)) {
            throw new AlreadyHasAccessException();
        }

        $movieAccess = new MovieAccess();
        $movieAccess->setMovie($movie);
        $movieAccess->setUser($user);
        $this->em->persist($movieAccess);
        return $movieAccess;
    }
}
