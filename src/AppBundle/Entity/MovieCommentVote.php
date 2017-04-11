<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\User;
use AppBundle\Entity\MovieComment;

use AppBundle\Interfaces\IVote;
use AppBundle\Interfaces\IVoter;
use AppBundle\Interfaces\IVotable;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MovieCommentVoteRepository")
 */
class MovieCommentVote implements IVote
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
     * @ORM\ManyToOne(targetEntity="User", inversedBy="movieCommentVotesCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $voter;

    /**
     * @var MovieComment
     *
     * @ORM\ManyToOne(targetEntity="MovieComment", inversedBy="movieCommentVotesCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $comment;

    public function setVotable(IVotable $votable)
    {
        $this->setComment($votable);

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
     * @return MovieCommentVote
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
     * @return MovieCommentVote
     */
    public function setVoter(IVoter $voter)
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
     * Set comment
     *
     * @param \AppBundle\Entity\MovieComment $comment
     *
     * @return MovieCommentVote
     */
    public function setComment(\AppBundle\Entity\MovieComment $comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return \AppBundle\Entity\MovieComment
     */
    public function getComment()
    {
        return $this->comment;
    }

    public function getVotable(): IVotable
    {
        return $this->getComment();
    }
}
