<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use AppBundle\Entity\Movie;
use AppBundle\Entity\MovieView;
use AppBundle\Entity\MovieVote;
use AppBundle\Entity\MovieOrder;
use AppBundle\Entity\MovieAccess;
use AppBundle\Entity\MovieComment;
use AppBundle\Entity\MovieCommentVote;

use AppBundle\Interfaces\VoterInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 *
 * Postgresql doesn't like selecting things from table called "user" so let's call it "users"
 * @ORM\Table(name="users")
 *
 * @UniqueEntity(fields="email", message="user.email.exists")
 * @UniqueEntity(fields="username", message="user.username.exists")
 */

class User implements UserInterface, VoterInterface
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
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\Length(min=3, max=255, minMessage="user.username.min_length", maxMessage="user.username.max_length")
     * @Assert\NotBlank(message="user.username.blank")
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Assert\NotBlank(message="user.email.blank")
     * @Assert\Email(message="user.email.invalid")
     */
    private $email;

    /**
     * @Assert\NotBlank(message="user.password.blank")
     * @Assert\Length(max=4096, maxMessage="user.password.max_length")
     */
    private $plainPassword;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    private $roles = [];

    /**
     * @var MovieView[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieView", mappedBy="user", cascade={"remove"})
     */
    private $movieViewsCollection;

    /**
     * @var Movie[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Movie", mappedBy="publisher", cascade={"remove"})
     */
    private $moviesCollection;

    /**
     * @var MovieAccess[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieAccess", mappedBy="user", cascade={"remove"})
     */
    private $movieAccessCollection;

    /**
     * @var MovieComment[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieComment", mappedBy="publisher", cascade={"remove"})
     */
    private $movieCommentsCollection;

    /**
     * @var MovieCommentVote[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieCommentVote", mappedBy="voter", cascade={"remove"})
     */
    private $movieCommentVotesCollection;

    /**
     * @var MovieOrder[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieOrder", mappedBy="user", cascade={"remove"})
     */
    private $movieOrdersCollection;

    /**
     * @var MovieVote[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MovieVote", mappedBy="voter", cascade={"remove"})
     */
    private $movieVotesCollection;

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param string $password
     */
    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }

    /**
     * Returns the roles or permissions granted to the user for security.
     */
    public function getRoles()
    {
        return empty($this->roles) ? ['ROLE_USER'] : $this->roles;
    }

    public function setRoles(array $roles)
    {
        $this->roles = array_unique($roles);
    }

    public function hasRole(string $role)
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        // if you had a plainPassword property, you'd nullify it here
        // $this->plainPassword = null;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->movieViewsCollection = new \Doctrine\Common\Collections\ArrayCollection();
        $this->moviesCollection = new \Doctrine\Common\Collections\ArrayCollection();
        $this->movieAccessCollection = new \Doctrine\Common\Collections\ArrayCollection();
        $this->movieCommentsCollection = new \Doctrine\Common\Collections\ArrayCollection();
        $this->movieCommentVotesCollection = new \Doctrine\Common\Collections\ArrayCollection();
        $this->movieOrdersCollection = new \Doctrine\Common\Collections\ArrayCollection();
        $this->movieVotesCollection = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add movieViewsCollection
     *
     * @param \AppBundle\Entity\MovieView $movieViewsCollection
     *
     * @return User
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
     * Add moviesCollection
     *
     * @param \AppBundle\Entity\Movie $moviesCollection
     *
     * @return User
     */
    public function addMoviesCollection(\AppBundle\Entity\Movie $moviesCollection)
    {
        $this->moviesCollection[] = $moviesCollection;

        return $this;
    }

    /**
     * Remove moviesCollection
     *
     * @param \AppBundle\Entity\Movie $moviesCollection
     */
    public function removeMoviesCollection(\AppBundle\Entity\Movie $moviesCollection)
    {
        $this->moviesCollection->removeElement($moviesCollection);
    }

    /**
     * Get moviesCollection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMoviesCollection()
    {
        return $this->moviesCollection;
    }

    /**
     * Add movieAccessCollection
     *
     * @param \AppBundle\Entity\MovieAccess $movieAccessCollection
     *
     * @return User
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
     * Add movieCommentsCollection
     *
     * @param \AppBundle\Entity\MovieComment $movieCommentsCollection
     *
     * @return User
     */
    public function addMovieCommentsCollection(\AppBundle\Entity\MovieComment $movieCommentsCollection)
    {
        $this->movieCommentsCollection[] = $movieCommentsCollection;

        return $this;
    }

    /**
     * Remove movieCommentsCollection
     *
     * @param \AppBundle\Entity\MovieComment $movieCommentsCollection
     */
    public function removeMovieCommentsCollection(\AppBundle\Entity\MovieComment $movieCommentsCollection)
    {
        $this->movieCommentsCollection->removeElement($movieCommentsCollection);
    }

    /**
     * Get movieCommentsCollection
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMovieCommentsCollection()
    {
        return $this->movieCommentsCollection;
    }

    /**
     * Add movieCommentVotesCollection
     *
     * @param \AppBundle\Entity\MovieCommentVote $movieCommentVotesCollection
     *
     * @return User
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

    /**
     * Add movieOrdersCollection
     *
     * @param \AppBundle\Entity\MovieOrder $movieOrdersCollection
     *
     * @return User
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
     * @return User
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
