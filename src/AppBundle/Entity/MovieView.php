<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MovieViewRepository")
 */
class MovieView
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var Movie
     *
     * @ORM\ManyToOne(targetEntity="Movie", inversedBy="movieViewsCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movie;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="movieViewsCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $viewedAt;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set viewedAt
     *
     * @param \DateTime $viewedAt
     *
     * @return MovieView
     */
    public function setViewedAt($viewedAt)
    {
        $this->viewedAt = $viewedAt;

        return $this;
    }

    /**
     * Get viewedAt
     *
     * @return \DateTime
     */
    public function getViewedAt()
    {
        return $this->viewedAt;
    }

    /**
     * Set movie
     *
     * @param \AppBundle\Entity\Movie $movie
     *
     * @return MovieView
     */
    public function setMovie(\AppBundle\Entity\Movie $movie)
    {
        $this->movie = $movie;

        return $this;
    }

    /**
     * Get movie
     *
     * @return \AppBundle\Entity\Movie
     */
    public function getMovie()
    {
        return $this->movie;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return MovieView
     */
    public function setUser(\AppBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
