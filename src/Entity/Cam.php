<?php

namespace App\Entity;

use App\Repository\CamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


use Symfony\Component\Serializer\Annotation\Groups;


use OpenApi\Attributes as OA;


#[ORM\Entity(repositoryClass: CamRepository::class)]
#[OA\Schema(
    schema: "Cam",
    properties: [
        new OA\Property(property: "id", type: "integer"),
        new OA\Property(property: "nom", type: "string"),
        new OA\Property(property: "videoUrl", type: "string"),
        new OA\Property(property: "ipCam", type: "string"),
        new OA\Property(property: "createdAt", type: "string", format: "date-time"),
        new OA\Property(property: "owner", ref: "#/components/schemas/Users")
    ]
)]
#[OA\Schema(
    schema: "CamInput",
    required: ["nom", "videoUrl", "ipCam"],
    properties: [
        new OA\Property(property: "nom", type: "string"),
        new OA\Property(property: "videoUrl", type: "string"),
        new OA\Property(property: "ipCam", type: "string")
    ]
)]
class Cam
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['cam:read', 'photo:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['cam:read', 'photo:read'])]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    #[Groups(['cam:read'])]
    private ?string $videoUrl = null;

    #[ORM\Column(length: 50)]
    #[Groups(['cam:read'])]
    private ?string $ipCam = null;

    #[ORM\Column]
    #[Groups(['cam:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'cams')]
    #[Groups(['cam:read'])]
    private ?Users $owner = null;

    #[ORM\OneToMany(mappedBy: 'cam', targetEntity: Photo::class)]
    private Collection $photos;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getVideoUrl(): ?string
    {
        return $this->videoUrl;
    }

    public function setVideoUrl(string $videoUrl): static
    {
        $this->videoUrl = $videoUrl;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

     public function getIpCam(): ?string
    {
        return $this->ipCam;
    }

    public function setIpCam(string $ipCam): static
    {
        $this->ipCam = $ipCam;

        return $this;
    }

    public function getOwner(): ?Users
    {
        return $this->owner;
    }

    public function setOwner(?Users $owner): static
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): static
    {
        if (!$this->photos->contains($photo)) {
            $this->photos->add($photo);
            $photo->setCam($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): static
    {
        if ($this->photos->removeElement($photo)) {
            if ($photo->getCam() === $this) {
                $photo->setCam(null);
            }
        }

        return $this;
    }
}
