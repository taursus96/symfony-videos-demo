<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MovieAccessRepository")
 */
class MovieAccess
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
     * @ORM\ManyToOne(targetEntity="Movie", inversedBy="movieAccessCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movie;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="movieAccessCollection")
     * @ORM\JoinColumn(nullable=false)
     *
     * @Assert\NotBlank(message="movie_access.user.doesnt_exist")
     */
    private $user;


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
     * Set movie
     *
     * @param \AppBundle\Entity\Movie $movie
     *
     * @return MovieAccess
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
     * @return MovieAccess
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
