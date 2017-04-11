<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Movie;

class MovieRepository extends \Doctrine\ORM\EntityRepository
{
    public function getFreeTopVoted(int $limit = 24)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AppBundle:Movie m WHERE m.access = :access ORDER BY m.thumbsUp DESC')
            ->setParameter(':access', Movie::ACCESS_FREE)
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getFreeTopViewed(int $limit = 24)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AppBundle:Movie m WHERE m.access = :access ORDER BY m.views DESC')
            ->setParameter(':access', Movie::ACCESS_FREE)
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getPaidTopVoted(int $limit = 24)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AppBundle:Movie m WHERE m.access = :access ORDER BY m.thumbsUp DESC')
            ->setParameter(':access', Movie::ACCESS_PAID)
            ->setMaxResults($limit)
            ->getResult();
    }

    public function getPaidTopViewed(int $limit = 24)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AppBundle:Movie m WHERE m.access = :access ORDER BY m.views DESC')
            ->setParameter(':access', Movie::ACCESS_PAID)
            ->setMaxResults($limit)
            ->getResult();
    }

    public function search(string $search)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT m FROM AppBundle:Movie m WHERE m.access != :privateAccess AND m.title LIKE :search')
            ->setParameter(':privateAccess', Movie::ACCESS_PRIVATE)
            ->setParameter(':search', '%' . $search . '%')
            ->getResult();
    }
}
