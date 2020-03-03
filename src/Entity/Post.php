<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Post implements OwnedResource
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"post_read", "write", "user_read"})
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @Groups({"post_read", "post_write", "user_read"})
     * @ORM\Column(type="text")
     */
    private $text;

    /**
     * @Groups({"post_read", "user_read"})
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @Groups({"post_read", "user_read"})
     * @ORM\Column(type="datetime")
     */
    private $updatedAt;

    /**
     * @Groups({"post_read", "user_read"})
     * @ORM\ManyToOne(targetEntity="App\Entity\District", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $district;

    /**
     * @Groups({"post_read"})
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    // todo: switch from tree to flat comments
    // 1. fetch all comments for this post, limit by depth
    // 2. build tree on frontend
    // 3.
    /**
     * @Groups({"post_read"})
     * @ORM\OneToMany(targetEntity="App\Entity\Comment", mappedBy="post", orphanRemoval=true)
     */
    private $comments;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalUpvotes;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalDownvotes;

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getOwner(): User
    {
        return $this->getAuthor();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function setUpdatedAtValue()
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function getRootComments(): Collection
    {
        return $this->comments->filter(function ($child) {
            /** @var Comment $child */
            return $child->getParent() === null;
        });
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setPost($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getPost() === $this) {
                $comment->setPost(null);
            }
        }

        return $this;
    }

    public function setComments(ArrayCollection $commentTree)
    {
        $this->comments = $commentTree;
    }

    public function getTotalUpvotes(): ?int
    {
        return $this->totalUpvotes;
    }

    public function setTotalUpvotes(int $totalUpvotes): self
    {
        $this->totalUpvotes = $totalUpvotes;

        return $this;
    }

    public function getTotalDownvotes(): ?int
    {
        return $this->totalDownvotes;
    }

    public function setTotalDownvotes(int $totalDownvotes): self
    {
        $this->totalDownvotes = $totalDownvotes;

        return $this;
    }
}
