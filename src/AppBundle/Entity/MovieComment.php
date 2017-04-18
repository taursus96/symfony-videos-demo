<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\User;
use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieCommentVote;

use AppBundle\Interfaces\VoteInterface;
use AppBundle\Interfaces\VotableInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MovieCommentRepository")
 */
class MovieComment implements VotableInterface
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
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\Length(
     *      min = 1,
     *      max = 2048,
     *      minMessage = "movie_comment.content.min_length",
     *      maxMessage = "movie_comment.content.max_length"
     * )
     */
    private $content;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $thumbsUp = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $thumbsDown = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $publishedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", fetch="EAGER", inversedBy="movieCommentsCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $publisher;

    /**
     * @var Movie
     *
     * @ORM\ManyToOne(targetEntity="Movie", inversedBy="comments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $movie;

    /**
     * @var MovieComment[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieCommentVote", mappedBy="comment", cascade={"remove"})
     */
    private $movieCommentVotesCollection;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
    }

    public function getVoteRepositoryName(): string
    {
        return 'AppBundle:MovieCommentVote';
    }

    public function getVoteVotablePropertyName(): string
    {
        return 'comment';
    }

    public function getVoteVoterPropertyName(): string
    {
        return 'voter';
    }

    public function createNewVoteEntity(): VoteInterface
    {
        return new MovieCommentVote();
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
     * Set content
     *
     * @param string $content
     *
     * @return MovieComment
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set thumbsUp
     *
     * @param integer $thumbsUp
     *
     * @return MovieComment
     */
    public function setThumbsUp(int $thumbsUp)
    {
        $this->thumbsUp = $thumbsUp;

        return $this;
    }

    /**
     * Get thumbsUp
     *
     * @return integer
     */
    public function getThumbsUp(): int
    {
        return $this->thumbsUp;
    }

    /**
     * Set thumbsDown
     *
     * @param integer $thumbsDown
     *
     * @return MovieComment
     */
    public function setThumbsDown(int $thumbsDown)
    {
        $this->thumbsDown = $thumbsDown;

        return $this;
    }

    /**
     * Get thumbsDown
     *
     * @return integer
     */
    public function getThumbsDown(): int
    {
        return $this->thumbsDown;
    }

    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     *
     * @return MovieComment
     */
    public function setPublishedAt($publishedAt)
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    /**
     * Get publishedAt
     *
     * @return \DateTime
     */
    public function getPublishedAt()
    {
        return $this->publishedAt;
    }

    /**
     * Set publisher
     *
     * @param \AppBundle\Entity\User $publisher
     *
     * @return MovieComment
     */
    public function setPublisher(\AppBundle\Entity\User $publisher)
    {
        $this->publisher = $publisher;

        return $this;
    }

    /**
     * Get publisher
     *
     * @return \AppBundle\Entity\User
     */
    public function getPublisher()
    {
        return $this->publisher;
    }

    /**
     * Set movie
     *
     * @param \AppBundle\Entity\Movie $movie
     *
     * @return MovieComment
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
     * Add movieCommentVotesCollection
     *
     * @param \AppBundle\Entity\MovieCommentVote $movieCommentVotesCollection
     *
     * @return MovieComment
     */
    public function addMovieCommentVotesCollection(\AppBundle\Entity\MovieCommentVote $movieCommentVotesCollection)
    {
        $this->movieCommentVotesCollection[] = $movieCommentVotesCollection;

        return $this;
    }

    /**
     * Remove movieCommentVotesCollection
     *
     * @param \AppBundle\Entity\MovieCommentVote $movieCommentVotesCollection
     */
    public function removeMovieCommentVotesCollection(\AppBundle\Entity\MovieCommentVote $movieCommentVotesCollection)
    {
        $this->movieCommentVotesCollection->removeElement($movieCommentVotesCollection);
    }

    /**
     * Get movieCommentVotesCollection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMovieCommentVotesCollection()
    {
        return $this->movieCommentVotesCollection;
    }
}
