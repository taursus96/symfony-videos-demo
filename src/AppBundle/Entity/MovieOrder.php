<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MovieOrderRepository")
 */
class MovieOrder
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
     * @ORM\ManyToOne(targetEntity="Movie", inversedBy="movieOrdersCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movie;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="movieOrdersCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var int
     * Movie price may change so it's important to have a price inside order that will not change
     *
     * @ORM\Column(type="integer", nullable=true)
     */
    private $price;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isCompleted = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $orderedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $completedAt;

    public function __construct()
    {
        $this->orderedAt = new \DateTime();
    }

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
     * Set price
     *
     * @param integer $price
     *
     * @return MovieOrder
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->price;
    }

    public function getPriceAsFloat()
    {
        return $this->price / 100;
    }

    /**
     * Set isCompleted
     *
     * @param boolean $isCompleted
     *
     * @return MovieOrder
     */
    public function setIsCompleted($isCompleted)
    {
        $this->isCompleted = $isCompleted;

        if ($isCompleted) {
            $this->setCompletedAt(new \DateTime());
        }

        return $this;
    }

    /**
     * Get isCompleted
     *
     * @return boolean
     */
    public function getIsCompleted()
    {
        return $this->isCompleted;
    }

    /**
     * Set orderedAt
     *
     * @param \DateTime $orderedAt
     *
     * @return MovieOrder
     */
    public function setOrderedAt($orderedAt)
    {
        $this->orderedAt = $orderedAt;

        return $this;
    }

    /**
     * Get orderedAt
     *
     * @return \DateTime
     */
    public function getOrderedAt()
    {
        return $this->orderedAt;
    }

    /**
     * Set completedAt
     *
     * @param \DateTime $completedAt
     *
     * @return MovieOrder
     */
    public function setCompletedAt($completedAt)
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    /**
     * Get completedAt
     *
     * @return \DateTime
     */
    public function getCompletedAt()
    {
        return $this->completedAt;
    }

    /**
     * Set movie
     *
     * @param \AppBundle\Entity\Movie $movie
     *
     * @return MovieOrder
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
     * @return MovieOrder
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
