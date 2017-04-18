<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\Movie;

use AppBundle\Interfaces\VoteInterface;
use AppBundle\Interfaces\VoterInterface;
use AppBundle\Interfaces\VotableInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MovieVoteRepository")
 */
class MovieVote implements VoteInterface
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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $vote = self::THUMBS_UP;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="movieVotesCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $voter;

    /**
     * @var Movie
     *
     * @ORM\ManyToOne(targetEntity="Movie", inversedBy="movieVotesCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movie;

    public function setVotable(VotableInterface $votable)
    {
        $this->setMovie($votable);

        return $this;
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
     * Set vote
     *
     * @param integer $vote
     *
     * @return MovieVote
     */
    public function setVote(int $vote)
    {
        $this->vote = $vote;

        return $this;
    }

    /**
     * Get vote
     *
     * @return integer
     */
    public function getVote(): int
    {
        return $this->vote;
    }

    /**
     * Set voter
     *
     * @param IVoter $voter
     *
     * @return MovieVote
     */
    public function setVoter(VoterInterface $voter)
    {
        $this->voter = $voter;

        return $this;
    }

    /**
     * Get voter
     *
     * @return \AppBundle\Entity\User
     */
    public function getVoter()
    {
        return $this->voter;
    }

    /**
     * Set movie
     *
     * @param \AppBundle\Entity\Movie $movie
     *
     * @return MovieVote
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

    public function getVotable(): VotableInterface
    {
        return $this->getMovie();
    }
}
