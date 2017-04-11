<?php

namespace AppBundle\Service;

use Doctrine\ORM\EntityManager;

use AppBundle\Exception\OrderingMovieThatIsNotOrderableException;
use AppBundle\Exception\OrderingMovieThatUserAlreadyHasAccessToException;
use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieOrder;
use AppBundle\Entity\User;

use AppBundle\Service\MovieAccessService;

class MovieOrderingService
{
    /** @var EntityManager $em */
    protected $em;
    /** @var MovieAccessService $movieAccessService */
    protected $movieAccessService;

    public function __construct(EntityManager $entityManager, MovieAccessService $movieAccessService)
    {
        $this->em = $entityManager;
        $this->movieAccessService = $movieAccessService;
    }

    public function order(User $user, Movie $movie): MovieOrder
    {
        if ($this->movieAccessService->hasAccess($user, $movie)) {
            throw new OrderingMovieThatUserAlreadyHasAccessToException();
        } elseif ($movie->getAccess() !== Movie::ACCESS_PAID) {
            throw new OrderingMovieThatIsNotOrderableException();
        }

        return $this->createOrder($user, $movie);
    }

    protected function createOrder(User $user, Movie $movie): MovieOrder
    {
        $order = new MovieOrder();
        $order->setUser($user);
        $order->setMovie($movie);
        //Movie price may change so it's important to have a price inside order that will not change
        $order->setPrice($movie->getPrice());

        $this->em->persist($order);
        return $order;
    }

    public function completeOrder(MovieOrder $order): bool
    {
        if ($order->getIsCompleted()) {
            return false;
        }

        $order->setIsCompleted(true);
        $this->movieAccessService->createAccess($order->getUser(), $order->getMovie());

        return true;
    }
}
