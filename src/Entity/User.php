<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(
 *  fields = {"email"},
 *  message = "app.ui.email_already"
 * )
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="app.ui.password_size")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="app.ui.password_confirm")
     */
    private $confirm_password;

    /**
     * @ORM\Column(type="string")
     */
    private $job;

    /**
     * @ORM\Column(type="datetime")
     */
    private $birthday;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min=4, max=30, minMessage="app.ui.error_web")
     */
    private $website;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min=4, max=30, minMessage="app.ui.error_github")
     */
    private $github;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min=4, max=30, minMessage="app.ui.error_twitter")
     */
    private $twitter;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min=4, max=30, minMessage="app.ui.error_instagram")
     */
    private $instagram;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\Length(min=4, max=30, minMessage="app.ui.error_facebook")
     */
    private $facebook;

    /**
     * @ORM\OneToMany(targetEntity=Post::class, mappedBy="user")
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity=PostComment::class, mappedBy="user")
     */
    private $postComments;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $pictureFileName;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->postComments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getConfirmPassword()
    {
        return $this->confirm_password;
    }

    public function setConfirmPassword($confirm_password): void
    {
        $this->confirm_password = $confirm_password;
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getSalt()
    {

    }

    public function eraseCredentials()
    {

    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setUser($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->removeElement($post)) {
            // set the owning side to null (unless already changed)
            if ($post->getUser() === $this) {
                $post->setUser(null);
            }
        }

        return $this;
    }

    public function getPostNumber(): ?int
    {
        return $this->posts->count();
    }

    /**
     * @return Collection|PostComment[]
     */
    public function getPostComments(): Collection
    {
        return $this->postComments;
    }

    public function addPostComment(PostComment $postComment): self
    {
        if (!$this->postComments->contains($postComment)) {
            $this->postComments[] = $postComment;
            $postComment->setUser($this);
        }

        return $this;
    }

    public function removePostComment(PostComment $postComment): self
    {
        if ($this->postComments->removeElement($postComment)) {
            // set the owning side to null (unless already changed)
            if ($postComment->getUser() === $this) {
                $postComment->setUser(null);
            }
        }

        return $this;
    }

    public function getPictureFilename()
    {
        return $this->pictureFileName;
    }

    public function setPictureFilename($pictureFileName): void
    {
        $this->pictureFileName = $pictureFileName;
    }

    public function hasPicture(): ?bool
    {
        return $this->pictureFileName != null;
    }

    /**
     * @return mixed
     */
    public function getJob()
    {
        return $this->job;
    }

    /**
     * @param mixed $job
     */
    public function setJob($job): void
    {
        $this->job = $job;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param mixed $birthday
     */
    public function setBirthday($birthday): void
    {
        $this->birthday = $birthday;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param mixed $website
     */
    public function setWebsite($website): void
    {
        $this->website = $website;
    }

    /**
     * @return mixed
     */
    public function getGithub()
    {
        return $this->github;
    }

    /**
     * @param mixed $github
     */
    public function setGithub($github): void
    {
        $this->github = $github;
    }

    /**
     * @return mixed
     */
    public function getTwitter()
    {
        return $this->twitter;
    }

    /**
     * @param mixed $twitter
     */
    public function setTwitter($twitter): void
    {
        $this->twitter = $twitter;
    }

    /**
     * @return mixed
     */
    public function getInstagram()
    {
        return $this->instagram;
    }

    /**
     * @param mixed $instagram
     */
    public function setInstagram($instagram): void
    {
        $this->instagram = $instagram;
    }

    /**
     * @return mixed
     */
    public function getFacebook()
    {
        return $this->facebook;
    }

    /**
     * @param mixed $facebook
     */
    public function setFacebook($facebook): void
    {
        $this->facebook = $facebook;
    }

    public function getAge()
    {
        $now = new \DateTime('now');
        $age = $this->getBirthday();
        $difference = $now->diff($age);

        return $difference->format('%y');
    }
}
