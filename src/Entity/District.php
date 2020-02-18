<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

// todo: this class will probably will have to be split into DTO and Doctrine entity, because of all
// the custom logic required for fetching posts
// also if hot/top/controversial are to be implemented probably should be exposed as subresources too?
/**
 * @ORM\Entity(repositoryClass="App\Repository\DistrictRepository")
 * @UniqueEntity(fields={"name"}, message="District already exists")
 */
class District implements OwnedResource
{
    // todo: consider removing and using name as id
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @Groups({"district_read"})
     * @ORM\Column(type="string", length=100, unique=true)
     */
    private $name;

    /**
     * @Groups({"district_read"})
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="district", orphanRemoval=true)
     */
    private $posts;

    // todo: add write for owners
    /**
     * @Groups({"district_read"})
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="districts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
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
            $post->setDistrict($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getDistrict() === $this) {
                $post->setDistrict(null);
            }
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getOwner(): User
    {
        return $this->owner;
    }

    public function setOwner(User $owner): self
    {
        $this->owner = $owner;

        return $this;
    }
}
