<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Entity\User;
use AppBundle\Entity\MovieView;
use AppBundle\Entity\MovieVote;
use AppBundle\Entity\MovieOrder;
use AppBundle\Entity\MovieAccess;
use AppBundle\Entity\MovieComment;

use AppBundle\Interfaces\VoteInterface;
use AppBundle\Interfaces\VotableInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MovieRepository")
 */
class Movie implements VotableInterface
{
    const ACCESS_FREE = 1;
    const ACCESS_PAID = 2;
    const ACCESS_PRIVATE = 3;

    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="movie.title.not_blank")
     * @Assert\Length(
     *      min = 3,
     *      max = 50,
     *      minMessage = "movie.title.min_length",
     *      maxMessage = "movie.title.max_length"
     * )
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=2048)
     *
     * @Assert\Length(
     *      min = 5,
     *      max = 2048,
     *      minMessage = "movie.description.min_length",
     *      maxMessage = "movie.description.max_length"
     * )
     */
    private $description;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(groups={"file_required"}, message="movie.file.not_blank")
     * @Assert\File(groups={"file_required"}, mimeTypes={ "video/mp4" }, mimeTypesMessage="movie.file.invalid_mime_type")
     */
    private $file;

    /**
      * @ORM\Column(type="string")
      *
      * @Assert\NotBlank(groups={"preview_required"}, message="movie.preview.not_blank")
      * @Assert\File(groups={"preview_required"}, mimeTypes={ "image/png", "image/jpeg", "image/gif" }, mimeTypesMessage="movie.preview.invalid_mime_type")
      */
    private $preview;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank(message="movie.access.not_blank")
     * @Assert\Choice(callback = "getAccessTypes", message="movie.access.invalid_access")
     */
    private $access = self::ACCESS_PRIVATE;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\NotBlank(groups={"price_required"}, message="movie.price.not_blank")
     * @Assert\GreaterThan(groups={"price_required"}, value=0, message="movie.price.lower_or_equal_zero")
     */
    private $price;

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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $views = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $publishedAt;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="moviesCollection")
     * @ORM\JoinColumn(nullable=false)
     */
    private $publisher;

    /**
     * @var MovieComment[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieComment", mappedBy="movie", cascade={"remove"})
     * @ORM\OrderBy({"publishedAt" = "DESC"})
     */
    private $comments;

    /**
     * @var MovieView[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieView", mappedBy="movie", cascade={"remove"})
     */
    private $movieViewsCollection;

    /**
     * @var MovieAccess[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieAccess", mappedBy="movie", cascade={"remove"})
     */
    private $movieAccessCollection;

    /**
     * @var MovieOrder[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieOrder", mappedBy="movie", cascade={"remove"})
     */
    private $movieOrdersCollection;

    /**
     * @var MovieVote[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieVote", mappedBy="movie", cascade={"remove"})
     */
    private $movieVotesCollection;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->comments = new ArrayCollection();
    }

    public function getVoteRepositoryName(): string
    {
        return 'AppBundle:MovieVote';
    }

    public function getVoteVotablePropertyName(): string
    {
        return 'movie';
    }

    public function getVoteVoterPropertyName(): string
    {
        return 'voter';
    }

    public function createNewVoteEntity(): VoteInterface
    {
        return new MovieVote();
    }

    public static function getAccessTypes(): array
    {
        return [
            self::ACCESS_FREE,
            self::ACCESS_PAID,
            self::ACCESS_PRIVATE
        ];
    }

    public function getPriceAsFloat()
    {
        return $this->price / 100;
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
     * Set title
     *
     * @param string $title
     *
     * @return Movie
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Movie
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set file
     *
     * @param string $file
     *
     * @return Movie
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set access
     *
     * @param integer $access
     *
     * @return Movie
     */
    public function setAccess($access)
    {
        $this->access = $access;

        return $this;
    }

    /**
     * Get access
     *
     * @return integer
     */
    public function getAccess()
    {
        return $this->access;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return Movie
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

    /**
     * Set thumbsUp
     *
     * @param integer $thumbsUp
     *
     * @return Movie
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
     * @return Movie
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
     * Set views
     *
     * @param integer $views
     *
     * @return Movie
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return integer
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set publishedAt
     *
     * @param \DateTime $publishedAt
     *
     * @return Movie
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
     * @return Movie
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
     * Add comment
     *
     * @param \AppBundle\Entity\MovieComment $comment
     *
     * @return Movie
     */
    public function addComment(\AppBundle\Entity\MovieComment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Remove comment
     *
     * @param \AppBundle\Entity\MovieComment $comment
     */
    public function removeComment(\AppBundle\Entity\MovieComment $comment)
    {
        $this->comments->removeElement($comment);
    }

    /**
     * Get comments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set preview
     *
     * @param string $preview
     *
     * @return Movie
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;

        return $this;
    }

    /**
     * Get preview
     *
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * Add movieViewsCollection
     *
     * @param \AppBundle\Entity\MovieView $movieViewsCollection
     *
     * @return Movie
     */
    public function addMovieViewsCollection(\AppBundle\Entity\MovieView $movieViewsCollection)
    {
        $this->movieViewsCollection[] = $movieViewsCollection;

        return $this;
    }

    /**
     * Remove movieViewsCollection
     *
     * @param \AppBundle\Entity\MovieView $movieViewsCollection
     */
    public function removeMovieViewsCollection(\AppBundle\Entity\MovieView $movieViewsCollection)
    {
        $this->movieViewsCollection->removeElement($movieViewsCollection);
    }

    /**
     * Get movieViewsCollection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMovieViewsCollection()
    {
        return $this->movieViewsCollection;
    }

    /**
     * Add movieAccessCollection
     *
     * @param \AppBundle\Entity\MovieAccess $movieAccessCollection
     *
     * @return Movie
     */
    public function addMovieAccessCollection(\AppBundle\Entity\MovieAccess $movieAccessCollection)
    {
        $this->movieAccessCollection[] = $movieAccessCollection;

        return $this;
    }

    /**
     * Remove movieAccessCollection
     *
     * @param \AppBundle\Entity\MovieAccess $movieAccessCollection
     */
    public function removeMovieAccessCollection(\AppBundle\Entity\MovieAccess $movieAccessCollection)
    {
        $this->movieAccessCollection->removeElement($movieAccessCollection);
    }

    /**
     * Get movieAccessCollection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMovieAccessCollection()
    {
        return $this->movieAccessCollection;
    }

    /**
     * Add movieOrdersCollection
     *
     * @param \AppBundle\Entity\MovieOrder $movieOrdersCollection
     *
     * @return Movie
     */
    public function addMovieOrdersCollection(\AppBundle\Entity\MovieOrder $movieOrdersCollection)
    {
        $this->movieOrdersCollection[] = $movieOrdersCollection;

        return $this;
    }

    /**
     * Remove movieOrdersCollection
     *
     * @param \AppBundle\Entity\MovieOrder $movieOrdersCollection
     */
    public function removeMovieOrdersCollection(\AppBundle\Entity\MovieOrder $movieOrdersCollection)
    {
        $this->movieOrdersCollection->removeElement($movieOrdersCollection);
    }

    /**
     * Get movieOrdersCollection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMovieOrdersCollection()
    {
        return $this->movieOrdersCollection;
    }

    /**
     * Add movieVotesCollection
     *
     * @param \AppBundle\Entity\MovieVote $movieVotesCollection
     *
     * @return Movie
     */
    public function addMovieVotesCollection(\AppBundle\Entity\MovieVote $movieVotesCollection)
    {
        $this->movieVotesCollection[] = $movieVotesCollection;

        return $this;
    }

    /**
     * Remove movieVotesCollection
     *
     * @param \AppBundle\Entity\MovieVote $movieVotesCollection
     */
    public function removeMovieVotesCollection(\AppBundle\Entity\MovieVote $movieVotesCollection)
    {
        $this->movieVotesCollection->removeElement($movieVotesCollection);
    }

    /**
     * Get movieVotesCollection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMovieVotesCollection()
    {
        return $this->movieVotesCollection;
    }
}
